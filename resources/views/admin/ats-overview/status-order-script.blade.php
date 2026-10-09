<script>
(function () {
    var lists = Array.from(document.querySelectorAll('[data-ats-stage-order-job]'), function (list) {
        return {
            element: list,
            key: 'ja-tab-order-' + list.getAttribute('data-ats-stage-order-job'),
            stages: Array.from(list.querySelectorAll('.ats-status-stage[data-stage-id]'))
        };
    });

    function applyOrder(list) {
        var saved;
        try { saved = JSON.parse(localStorage.getItem(list.key)); }
        catch (error) { saved = null; }

        var order = list.stages;
        if (Array.isArray(saved) && saved.length) {
            var stages = new Map(list.stages.map(function (stage) {
                return [stage.getAttribute('data-stage-id'), stage];
            }));
            var ids = saved.map(String);
            // Match Job Applications: ignore an order containing deleted stages.
            if (new Set(ids).size === ids.length && ids.every(function (id) { return stages.has(id); })) {
                // Its tab bar appends saved stages after any newly added stages.
                order = list.stages.filter(function (stage) {
                    return ids.indexOf(stage.getAttribute('data-stage-id')) === -1;
                }).concat(ids.map(function (id) { return stages.get(id); }));
            }
        }
        if (order.every(function (stage, index) { return list.element.children[index] === stage; })) return;
        order.forEach(function (stage) { list.element.appendChild(stage); });
    }

    window.atsApplyStoredStageOrders = function (wrapper) {
        lists.forEach(function (list) {
            if (!wrapper || wrapper.contains(list.element)) applyOrder(list);
        });
    };
    window.addEventListener('storage', function (event) {
        if (event.key === null || (event.key && event.key.indexOf('ja-tab-order-') === 0)) {
            window.atsApplyStoredStageOrders();
        }
    });
    window.addEventListener('focus', function () { window.atsApplyStoredStageOrders(); });
    window.addEventListener('pageshow', function () { window.atsApplyStoredStageOrders(); });
    window.atsApplyStoredStageOrders();
})();
</script>
