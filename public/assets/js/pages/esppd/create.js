$(document).ready(function () {
	$(".kt_datepicker").flatpickr({
		enableTime: false,
		dateFormat: "Y-m-d",
	});

	$("#kt_docs_repeater_advanced").repeater({
		initEmpty: false,

		defaultValues: {
			"text-input": "foo",
		},

		show: function () {
			$(this).slideDown();

			// Re-init select2
			$(this).find('[data-kt-repeater="select2"]').select2();

			// Re-init flatpickr
			$(this).find('[data-kt-repeater="datepicker"]').flatpickr();
		},

		hide: function (deleteElement) {
			Swal.fire({
				title: "Apakah Anda yakin?",
				text: "Anda tidak akan bisa mengembalikan ini!",
				icon: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: "Ya, hapus!",
			}).then((result) => {
				if (result.isConfirmed) {
					$(this).slideUp(deleteElement);
				}
			});
		},

		ready: function () {
			// Init select2
			$('[data-kt-repeater="select2"]').select2();

			// Init flatpickr
			$('[data-kt-repeater="datepicker"]').flatpickr();
		},

		isFirstItemUndeletable: true,
	});

	var myDropzone = new Dropzone("#kt_dropzonejs_example_1", {
		url: appUrl + "esppd/upload",
		autoProcessQueue: false,
		paramName: "file",
		maxFiles: 10,
		maxFilesize: 10,
		acceptedFiles: ".pdf,.jpg,.jpeg,.png",
		addRemoveLinks: true,
		accept: function (file, done) {
			if (file.name === "wow.jpg") {
				done("Naha, you don't.");
			} else {
				done();
			}
		},
	});

	// Upload akan diproses saat submit tombol, bukan otomatis

	// on submit
	$(".btn-submit").on("click", function (e) {
		e.preventDefault();

		const formEl = $("form")[0];
		const fd = new FormData(formEl);

		fd.append("data_type", $(this).data("type") ?? "");

		// validasi form (kecuali catatan)
		var errors = [];
		var travelType = $('select[name="travel_type"]').val();
		if (!travelType) {
			errors.push("Jenis perjalanan wajib diisi");
		}

		var items = $(
			'[data-repeater-list="kt_docs_repeater_advanced"] [data-repeater-item]'
		);
		if (items.length === 0) {
			errors.push("Minimal satu destinasi perjalanan");
		} else {
			items.each(function () {
				$(this)
					.find('[data-kt-repeater="select2"]')
					.each(function () {
						var v = $(this).val();
						if (v === null || v === "") {
							errors.push("Field destinasi wajib diisi");
							return false;
						}
					});
				$(this)
					.find('[data-kt-repeater="datepicker"]')
					.each(function () {
						var v = ($(this).val() || "").trim();
						if (v === "") {
							errors.push("Tanggal perjalanan wajib diisi");
							return false;
						}
					});
			});
		}

		var preApprover = $("#pre_approver").val();
		if (!preApprover) {
			errors.push("Pemberi Persetujuan Awal wajib diisi");
		}

		var postApprover = $("#post_approver").val();
		if (!postApprover) {
			errors.push("Pemberi Persetujuan Akhir wajib diisi");
		}

		if (errors.length) {
			Swal.fire({
				icon: "error",
				title: "Oops...",
				text: errors[0],
			});
			return;
		}

		myDropzone.getAcceptedFiles().forEach(function (f) {
			fd.append("file[]", f, f.name);
		});

		const apiUploadUrl = "http://esppd.test/api/travel_request/create";

		// ajax
		$.ajax({
			url: apiUploadUrl,
			type: "POST",
			data: fd,
			processData: false,
			contentType: false,
			success: function (response) {
				// success
				if (response.success) {
					// show success message
					Swal.fire({
						icon: "success",
						title: "Sukses",
						text: "Permintaan perjalanan berhasil diajukan",
					});
					// redirect to index
					setTimeout(function () {
						window.location.href = appUrl + "esppd";
					}, 2000);
				} else {
					// show error message
					Swal.fire({
						icon: "error",
						title: "Oops...",
						text: response.message,
					});
				}
			},
		});
	});
});
