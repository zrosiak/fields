$(function(){
    $(document).on('click', '.scoretable-action .icon-plus-square', function(){
        var count = $(this).parents('table').data('count') + 1;
        var $elem = $(this).parents('tbody').find('tr').eq(0).clone();

        $elem.find('td').each(function(){
            var $input = $(this).find('input');

            if ($input.attr('name')) {
                $input
                    .attr('name', $input.attr('name').replace(/\[[0-9]\]\[/, '[' + count + ']['))
                    .val('');
            }
        });

        $(this).parents('tr').after($elem);
        $(this).parents('table').data('count', count)
    });

    $(document).on('click', '.scoretable-action .icon-minus-square', function(){
        $(this).parents('tr').remove();
    });

    /*$(".scoretable tbody").sortable({
        group: 'scoretable-list',
        handle: 'i.icon-align-justify',
        items: "> tr",
        onDragStart: function ($item, container, _super) {
            $item.clone().insertAfter($item);
            _super($item, container);
        }
    });*/

    /*$('.scoretable tbody').sortable({
        items: "> tr"
    });*/
});