$(function () {
    var dialog, form;
    var selectedDsoId = 0;

    function prependDialogForm() {
        var checkboxesHtml = '<p>Existing observing lists:</p>';
        $.ajax({
            type: "GET",
            url: Routing.generate('dso_planner_async_retrieve_observing_list', { dsoId: selectedDsoId }),
            dataType: "json",
            success: function (savedLists) {
                console.log(savedLists);
                if (savedLists.length === 0) {
                    $('#existingPlannedLists').html("<p>There are no observing lists yet. Add new one bellow.</p>")
                } else {
                    savedLists.forEach(function (plannedList) {
                        var checked = " ";
                        if (plannedList.dsoOnList) {
                            checked = ' checked ';
                        }
                        checkboxesHtml += '<div class="checkbox"> <input type="checkbox" value="' + plannedList.listId + '" ' + checked + '> ' + plannedList.listName + ' </div>';
                    });
                    $('#existingPlannedLists').html(checkboxesHtml);
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    }

    dialog = $("#dialog-form").dialog({
        autoOpen: false,
        height: 450,
        width: 400,
        modal: false,
        buttons: {
            "Save": function () {
                $("#dialog-form").submit();
            },
            Cancel: function () {
                dialog.dialog("close");
            }
        },
        close: function () {
            $(this).find( "form :text" ).val("");
        },
        open: function () {
            prependDialogForm();
        }
    });

    $("#dialog-form").submit(function (event) {
        event.preventDefault();
        var newListName = $(this).find('#list_name').val();
        var selectedCheckboxes = $(this).find('input:checkbox:checked').map(function () {
            return this.value;
        }).get();

        if (newListName === "" && selectedCheckboxes.length === 0) {
            $("#dialog-form").dialog("close");
            return;
        }

        $.ajax({
            type: "POST",
            url: Routing.generate('dso_planner_async_add_item_to_list'),
            data: {
                selectedDsoId: selectedDsoId,
                listName: newListName,
                listIds: selectedCheckboxes
            },
            dataType: "json",
            success: function (response) {
                console.log(response);
                dialog.dialog("close");
                $("#dialog-message-ok").dialog({
                    width: 400,
                    modal: false,
                    buttons: {
                        Ok: function () {
                            $(this).dialog("close");
                        }
                    },
                    open: function () {
                        setTimeout("$('#dialog-message-ok').dialog('close')", 2000);
                    },
                    hide: {effect: "fade", duration: 800}
                });
            },
            error: function (response) {
                console.log(response);
                dialog.dialog("close");
                $("#dialog-message-error").dialog({
                    width: 450,
                    modal: false,
                    buttons: {
                        Ok: function () {
                            $(this).dialog("close");
                        }
                    },
                    open: function () {
                        setTimeout("$('#dialog-message-error').dialog('close')", 2000);
                    },
                    hide: {effect: "fade", duration: 800}
                });
            }
        });
    });

    $(".add-item-to-list").button().on("click", function (e) {
        selectedDsoId = e.currentTarget.dataset.dsoid;
        dialog.dialog("open");
    });
});
