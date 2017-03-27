/* Ugly fix for the pagination styling on
    /observations-log/logged-objects/
    /observations-log/obs-list/
 pages. */
var bootstrap2Pagination = $(".jquery-custom-class-identifier-ul .pagination ul").first();
if (bootstrap2Pagination) {
    bootstrap2Pagination.addClass('pagination');
}

/*
    Datetimepicker handlers.
 */
if (undefined !== $('#datetimepicker').val()) {
    debugger;
    $('#datetimepicker').datetimepicker({
        format:'Y-m-d H:i',
        startDate:new Date(),
        step: 15,
        theme: 'dark'
    });
}
if (undefined !== $('#form_start').val()) {
    debugger;
    $('#form_start').datetimepicker({
        format:'Y-m-d H:i',
        startDate:new Date(),
        step: 10,
        theme: 'dark'
    });
}
if (undefined !== $('#form_end').val()) {
    debugger;
    $('#form_end').datetimepicker({
        format:'Y-m-d H:i',
        startDate:new Date(),
        step: 10,
        theme: 'dark'
    });
}
