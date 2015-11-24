/* Ugly fix for the pagination styling on
    /observations-log/logged-objects/
    /observations-log/obs-list/
 pages. */
var bootstrap2Pagination = $(".jquery-custom-class-identifier-ul .pagination ul").first();
if (bootstrap2Pagination) {
    bootstrap2Pagination.addClass('pagination');
}
