"use strict";

$(document).ready(function () {
	var table = $("#kt_table_positions").DataTable({
		lengthChange: false,
		searching: true,
		ordering: false,
		info: true,
		autoWidth: false,
		responsive: true,
		dom: "lrtip", // Hide default search box
	});

	$('[data-kt-user-table-filter="search"]').on("keyup", function () {
		table.search(this.value).draw();
	});

	// Manage Submission
	$("form").on("submit", function (e) {
		e.preventDefault();

		// ajax
		$.ajax({
			url: appUrl + "master/positions/ajax",
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
						window.location.href = appUrl + "master/positions";
					});
				}
			},
		});
	});
});
