"use strict";

$(document).ready(function () {
	// Manage Submission
	$("form").on("submit", function (e) {
		e.preventDefault();

		// ajax
		$.ajax({
			url: appUrl + "master/groups/ajax",
			type: "POST",
			data: $(this).serialize(),
			success: function (response) {
				console.log(response);
				if (response.success) {
					// show success message
					Swal.fire({
						icon: "success",
						title: "Success",
						text: response.message,
					}).then(function () {
						// redirect to index
						window.location.href = appUrl + "master/groups";
					});
				}
			},
		});
	});
});
