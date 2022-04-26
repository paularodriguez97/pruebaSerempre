(function($) {
    jQuery.validator.addMethod("letters", function(value, element) {
        return this.optional(element) || /[a-zA-Z ]/.test(value);
    }, "Please only fill in characters from A - Z");

    $("#my_form").validate({
        rules: {
            name: {
                required: true,
                letters: true
            },
        },
        messages: {
            name: {
                required: "Fill in the username in the field",
                letters: "Please only fill in characters from A - Z"
            },
        }
    });
})(jQuery);
