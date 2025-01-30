<script>
(function($) {

	"use strict";


	// Events
$('.dropdown-container')
	.on('click', '.dropdown-button', function() {
        $(this).siblings('.dropdown-list').toggle();
	})
	.on('input', '.dropdown-search', function() {
    	var target = $(this);
        var dropdownList = target.closest('.dropdown-list');
    	var search = target.val().toLowerCase();

    	if (!search) {
            dropdownList.find('li').show();
            return false;
        }

    	dropdownList.find('li').each(function() {
        	var text = $(this).text().toLowerCase();
            var match = text.indexOf(search) > -1;
            $(this).toggle(match);
        });
	})
	.on('change', '[type="checkbox"]', function() {
        var container = $(this).closest('.dropdown-container');
        var numChecked = container. find('[type="checkbox"]:checked').length;
    	container.find('.quantity').text(numChecked || 'Any');
	});

// JSON of States for demo purposes
var usStates =
  <?=json_encode($courses)?>
;
//  { name: 'ALABAMA', abbreviation: 'AL'},
// <li> template
var stateTemplate = _.template(
    '<li>' +
    	'<label class="checkbox-wrap"><input name="courses[]" value = "<%= abbreviation %>" type="checkbox"> <span for="<%= abbreviation %>"><%= capName %> (<%= accro %>)</span> <span class="checkmark"></span></label>' +
    	// '<label for="<%= abbreviation %>"><%= capName %></label>' +
    '</li>'
);

// Populate list with states
_.each(usStates, function(s) {
    s.capName = _.startCase(s.name.toLowerCase());
    $('#coursesList').append(stateTemplate(s));
});

})(jQuery);

</script>
