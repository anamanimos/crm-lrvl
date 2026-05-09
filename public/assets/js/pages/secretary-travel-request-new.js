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
		url: appUrl + "/secretary/travel_request/upload_documents", // Set the url for your upload script location
		paramName: "file", // The name that will be used to transfer the file
		maxFiles: 10,
		maxFilesize: 10, // MB
		acceptedFiles: ".pdf,.jpg,.jpeg,.png",
		addRemoveLinks: true,
		accept: function (file, done) {
			if (file.name == "wow.jpg") {
				done("Naha, you don't.");
			} else {
				done();
			}
		},
	});

	myDropzone.on("success", function (file, response) {
		if (response && response.success && response.data) {
			file._server = response.data;
		}
	});

	// on submit
	$(".btn-submit").on("click", function (e) {
		e.preventDefault();

		const formData = (function ($form) {
			const obj = {};
			const arr = $form.serializeArray();
			arr.forEach(function (field) {
				const name = field.name;
				const value = field.value;
				if (/\[\]$/.test(name)) {
					const key = name.replace(/\[\]$/, "");
					if (!obj[key]) obj[key] = [];
					obj[key].push(value);
				} else if (obj[name] !== undefined) {
					if (!Array.isArray(obj[name])) obj[name] = [obj[name]];
					obj[name].push(value);
				} else {
					obj[name] = value;
				}
			});
			return obj;
		})($("form"));

		formData.documents = myDropzone.getAcceptedFiles().map(function (f) {
			return {
				name: f.name,
				size: f.size,
				type: f.type,
				stored_name: f._server ? f._server.stored_name : null,
				url: f._server ? f._server.file_url : null,
				original_name: f._server ? f._server.original_name : f.name,
			};
		});

		formData.data_type = $(this).data("type") ?? null;

		// validasi pastikan semua field diisi kecuali note
		if (
			Object.values(formData).some(
				(v) => v === null || (v === "" && v !== formData.note)
			)
		) {
			Swal.fire({
				icon: "error",
				title: "Oops...",
				text: "Anda harus mengisi semua field!",
			});
			return;
		}

		// ajax
		$.ajax({
			url: appUrl + "secretary/travel_request/ajax",
			type: "POST",
			data: formData,
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
						window.location.href = appUrl + "secretary/travel_request";
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
