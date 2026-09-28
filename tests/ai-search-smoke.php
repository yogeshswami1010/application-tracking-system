<?php
// php tests/ai-search-smoke.php — in-memory database, no live AI requests.
require __DIR__.'/../vendor/autoload.php';

use App\Http\Controllers\Admin\AdminJobApplicationController;
use App\Services\CandidateSearchText as Text;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

function check($condition, $label) { if (!$condition) throw new RuntimeException($label); echo "PASS: $label\n"; }
$app = new Application(dirname(__DIR__));
$app->instance('config',new Repository());
Facade::setFacadeApplication($app);
$db=new Capsule($app);
$db->addConnection(['driver'=>'sqlite','database'=>':memory:','prefix'=>'']);
$db->setAsGlobal();
$db->bootEloquent();
$app->instance('db',$db->getDatabaseManager());
$validator=new Illuminate\Validation\Factory(new Illuminate\Translation\Translator(new Illuminate\Translation\ArrayLoader(),'en'));
Request::macro('validate',function($rules) use($validator){return $validator->make($this->all(),$rules)->validate();});
Illuminate\Support\Facades\Cache::swap(new Illuminate\Cache\Repository(new Illuminate\Cache\ArrayStore()));
$db->schema()->create('job_applications',function(Blueprint $t){
    $t->id();
    foreach(['full_name','address','city','state','country','cv_text','cv_job_titles','cv_skills_text','cv_location_text'] as $field) $t->text($field)->nullable();
    foreach(['status_id','job_id','location_id'] as $field) $t->integer($field)->nullable();
    $t->text('skills')->nullable();
    $t->float('cv_experience_years')->nullable();
    $t->boolean('is_candidate')->default(false);
    $t->timestamp('cv_indexed_at')->nullable();
    $t->timestamp('moved_to_trash_at')->nullable();
    $t->softDeletes();
    $t->timestamps();
});
$db->schema()->create('skills',function(Blueprint $t){$t->id();$t->string('name');});
$db->schema()->create('application_status',function(Blueprint $t){$t->id();$t->string('status');$t->string('color');});
$db->schema()->create('jobs',function(Blueprint $t){$t->id();$t->string('title');});
$db->schema()->create('job_locations',function(Blueprint $t){$t->id();$t->string('location');});
$controller=new class extends AdminJobApplicationController {
    public function __construct() { $this->user=new class {public function cans($permission){return true;}}; }
};
function search($query,$extra=[]) {
    global $controller;
    return $controller->aiSearchResults(Request::create('/','GET',array_merge(['query'=>$query],$extra)))['results'];
}
$db->table('skills')->insert(['id'=>1,'name'=>'310 J']);
$fixtures=[
 ['id'=>1,'full_name'=>'Exact code','cv_text'=>'Certified 310J. Trailer technician'],
 ['id'=>2,'full_name'=>'Spaced code','cv_text'=>'Qualified 310 j trailer technician'],
 ['id'=>3,'full_name'=>'Different code','cv_text'=>'310S automotive mechanic'],
 ['id'=>4,'full_name'=>'Tagged only','skills'=>'["1"]','is_candidate'=>true],
 ['id'=>5,'full_name'=>'Structured only','cv_skills_text'=>'310-j'],
 ['id'=>6,'full_name'=>'No qualification','cv_text'=>'Experienced trailer technician'],
 ['id'=>7,'full_name'=>'Exact role','cv_job_titles'=>'Welder','cv_text'=>'Fabrication specialist','city'=>'Toronto'],
 ['id'=>8,'full_name'=>'Typo role','cv_job_titles'=>'Wleder','city'=>'Toronto'],
 ['id'=>9,'full_name'=>'Split words','cv_job_titles'=>'Forklift operator'],
 ['id'=>10,'full_name'=>'Wrong location','cv_job_titles'=>'Welder','city'=>'Vancouver'],
 ['id'=>11,'full_name'=>'Deleted','cv_text'=>'310J','deleted_at'=>'2026-01-01'],
 ['id'=>12,'full_name'=>'In trash','cv_text'=>'310J','moved_to_trash_at'=>'2026-01-01'],
 ['id'=>13,'full_name'=>'Jenna Smith'],
 ['id'=>14,'full_name'=>'False short substring','cv_text'=>'Spark marketing cloud'],
];
foreach($fixtures as $fixture)$db->table('job_applications')->insert($fixture);
foreach(['310j','310J','310 j',' 310-J '] as $query){
 $parsed=$controller->aiParseQuery(Request::create('/','POST',['query'=>$query]));
 check($parsed['keywords']===['310j'] && $parsed['roles']===[],'Deterministic parsing: '.$query);
 $result=search($query);
 if(!isset($baseline))$baseline=$result;
 check($result===$baseline,'Identical IDs, ordering and scores: '.$query);
}
$ids=array_column($baseline,'id');sort($ids);
check($ids===[1,2,4,5],'CV, tagged and structured skills match; other codes and deleted records excluded');
$expanded=search('310 j',['roles'=>['trailer technician'],'terms'=>['mechanic','310J']]);
check(!in_array(6,array_column($expanded,'id')),'AI synonyms do not bypass the requested qualification code');
check(in_array(7,array_column(search('wleder'),'id')),'Transposed letters recover a matching role');
check(in_array(9,array_column(search('fork lift operator'),'id')),'Split words match a joined job title');
check(array_column(search('jenna smtih'),'id')===[13],'Candidate name typos are matched without requiring a CV role');
check(search('sql')===[],'Short abbreviations do not match unrelated text');
$roles=search('welder',['location'=>'tornto']);
check(array_column($roles,'id')===[7,8] && $roles[0]['score']>$roles[1]['score'],'Exact role ranks above a typo; location typos work and other cities are excluded');
check(Text::codes('10 yrs experience')===[],'Experience units are not qualification codes');
check(Text::strength('C++ developer','c++')===1.0 && Text::strength('C# developer','c++')===0.0,'Distinct programming-language symbols are preserved');
check(Text::strength('mechanical technician','mechnical technician')>0,'Missing letters in role phrases are tolerated');
// Ensure the old 2000-record cutoff cannot hide a late matching candidate.
for($offset=0;$offset<2100;$offset+=100){
 $rows=[];
 for($i=1;$i<=100;$i++)$rows[]=['id'=>100+$offset+$i,'full_name'=>'Unrelated '.$i,'cv_text'=>'Office administration'];
 $db->table('job_applications')->insert($rows);
}
$db->table('job_applications')->insert(['id'=>9000,'full_name'=>'Late candidate','cv_text'=>'310J']);
check(in_array(9000,array_column(search('310 j'),'id')),'Matches beyond the previous 2000-record cap are included');

$app['config']->set('services.deepseek.key','fake-test-key');
$providerCalls=0;
$http=new Illuminate\Http\Client\Factory();
$http->fake(function() use (&$providerCalls,$http) {
    $providerCalls++;
    return $http->response(['choices'=>[['message'=>['content'=>json_encode([
        'skills'=>['WELDING'],'keywords'=>['Fabrication'],'roles'=>['Welder'],
        'location'=>'','min_experience'=>0
    ])]]]],200);
});
Illuminate\Support\Facades\Http::swap($http);
$first=$controller->aiParseQuery(Request::create('/','POST',['query'=>'Find WELDERS']));
$second=$controller->aiParseQuery(Request::create('/','POST',['query'=>' find   welders ']));
check($first===$second && $providerCalls===1,'Equivalent natural-language queries reuse the same normalized AI interpretation');
check($first['roles']===['welder'] && $first['skills']===['welding'],'AI role and skill expansions are normalized');
check(in_array(7,array_column(search('find welders',['roles'=>$first['roles'],'terms'=>array_merge($first['skills'],$first['keywords'])]),'id')),'Natural-language AI interpretation retrieves relevant candidates');

echo "All AI Search regression checks passed.\n";
