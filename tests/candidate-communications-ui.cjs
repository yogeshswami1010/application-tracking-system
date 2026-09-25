const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');

// Small DOM harness exercises the composer without a live ATS database.
class Element {
    constructor() { this.value=''; this.disabled=false; this.dataset={}; this.handlers={}; this.children=[]; this.textContent=''; }
    addEventListener(type, handler) { this.handlers[type]=handler; }
    replaceChildren(...items) { this.children=items; }
    append(item) { this.children.push(item); }
    add(item) { this.children.push(item); }
    showModal() { this.open=true; }
    close() { this.open=false; }
}
const ids=['count','feedback','template','message','subject','template-name','title','email-fields','summary','recipients','send','dialog','save-template','close','close-top','template-panel','form'];
const elements=Object.fromEntries(ids.map(id=>['cc-'+id,new Element()]));
const root=new Element();
root.dataset={previewUrl:'/preview',sendUrl:'/send',templatesUrl:'/templates',token:'test',source:'candidates'};
const buttons=['email','sms'].map(channel=>{const button=new Element();button.dataset.ccOpen=channel;return button;});
root.querySelectorAll=()=>buttons;
elements['candidate-communications']=root;
elements['cc-form'].querySelectorAll=()=>ids.filter(id=>!['form','dialog'].includes(id)).map(id=>elements['cc-'+id]);
const selected=[{type:'application',id:7},{type:'registration',id:7}];
const requests=[];
let resolveSend, delaySend=false;
const context={
    console, Option: class { constructor(text,value){this.text=text;this.value=value;} },
    document:{
        getElementById:id=>elements[id],
        querySelectorAll:()=>[],
        createElement:()=>new Element(),
        addEventListener:()=>{},
    },
    localStorage:{getItem:()=>JSON.stringify([{name:'Old invite',subject:'Old subject',message:'Old message'}])},
    fetch:async(url,options)=>{
        const data=options.body?JSON.parse(options.body):null;
        requests.push({url,data});
        if(url==='/preview')return {ok:true,json:async()=>({recipients:data.recipients.map((r,i)=>({...r,name:'Candidate '+i,address:i+'@example.test',reason:null}))})};
        if(url==='/templates')return {ok:true,json:async()=>({templates:[{id:3,name:'Invite',subject:'Hello',message:'Hi [applicant_name]'}]})};
        if(delaySend){delaySend=false;await new Promise(resolve=>{resolveSend=resolve;});}
        return {ok:true,json:async()=>({results:[{status:'sent'}]})};
    },
};
context.window=context;
context.addEventListener=()=>{};
context.ccSelection=()=>selected;
vm.runInNewContext(fs.readFileSync('public/js/candidate-communications.js','utf8'),context);
(async()=>{
    assert.equal(elements['cc-count'].textContent,2);
    await context.ccOpen('email');
    assert.equal(elements['cc-subject'].required,true);
    assert.equal(elements['cc-template'].children.length,3,'Includes server and legacy templates');
    assert.deepEqual(requests[0].data.recipients,selected,'Mixed candidate types retain distinct identities');
    elements['cc-template'].value='3';
    elements['cc-template'].handlers.change();
    assert.equal(elements['cc-message'].value,'Hi [applicant_name]');
    assert.equal(elements['cc-subject'].value,'Hello');
    await elements['cc-save-template'].handlers.click();
    assert.equal(requests.filter(r=>r.url==='/send').length,0,'Saving a template never sends it');
    delaySend=true;
    const sending=elements['cc-form'].handlers.submit({preventDefault(){}});
    await Promise.resolve();
    await elements['cc-form'].handlers.submit({preventDefault(){}});
    assert.equal(requests.filter(r=>r.url==='/send').length,1,'Double click does not create a second send');
    resolveSend();
    await sending;
    assert.equal(requests.filter(r=>r.url==='/send').length,2,'One request per selected recipient');
    assert.equal(elements['cc-send'].disabled,true,'Completed batch cannot be resubmitted');
    assert.match(elements['cc-feedback'].textContent,/2 sent, 0 failed/);
    elements['cc-close-top'].handlers.click();
    assert.equal(elements['cc-dialog'].open,false,'Top-right close button dismisses the popup');
    await context.ccOpen('sms');
    assert.equal(elements['cc-message'].maxLength,1600);
    assert.equal(elements['cc-subject'].required,false);
    assert.equal(elements['cc-email-fields'].hidden,true);
    assert.equal(elements['cc-message'].value,'','Email draft is not reused as an SMS');
    // Exercise the actual markup fallback, including when an older script is cached.
    const markup = fs.readFileSync('resources/views/admin/partials/candidate-communications.blade.php','utf8');
    const closeHandler = markup.match(/id="cc-close-top" onclick="([^"]+)"/)[1];
    for (const channel of ['email','sms']) {
        elements['cc-dialog'].close();
        await context.ccOpen(channel);
        assert.equal(elements['cc-dialog'].open,true);
        new Function(closeHandler).call({closest:()=>elements['cc-dialog']});
        assert.equal(elements['cc-dialog'].open,false,'Cross closes '+channel+' without relying on a JS listener');
    }
    const profileRecipient = [{type:'application',id:42}];
    await context.ccOpen('email', profileRecipient);
    assert.deepEqual(requests.at(-2).data.recipients,profileRecipient,'Profile uses only the current candidate despite bulk selection');
    assert.equal(elements['cc-title'].textContent,'Send email');
    elements['cc-message'].value='Profile message';
    elements['cc-subject'].value='Profile subject';
    await elements['cc-form'].handlers.submit({preventDefault(){}});
    assert.deepEqual(requests.at(-1).data.recipients,profileRecipient,'Profile sends only to its candidate');
    elements['cc-dialog'].close();
    await context.ccOpen('email');
    assert.deepEqual(requests.at(-2).data.recipients,selected,'Bulk selection remains unchanged after profile email');
    elements['cc-dialog'].close();
    console.log('PASS: Composer selection, mixed sources, template reuse/save, duplicate-click protection, progress, and SMS mode');
})().catch(error=>{console.error(error);process.exitCode=1;});
