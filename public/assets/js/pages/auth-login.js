"use strict";
var KTSigninGeneral = (function () {
	var form;
	var submitButton;
	var validation;

	var handleValidation = function (e) {
		validation = FormValidation.formValidation(form, {
			fields: {
				user_key: {
					validators: {
						notEmpty: { message: "Email/Username harus diisi" },
					},
				},
				password: {
					validators: { notEmpty: { message: "Password harus diisi" } },
				},
			},
			plugins: {
				trigger: new FormValidation.plugins.Trigger(),
				bootstrap: new FormValidation.plugins.Bootstrap5({
					rowSelector: ".fv-row",
					eleInvalidClass: "",
					eleValidClass: "",
				}),
			},
		});
	};

	var handleSubmit = function (e) {
		submitButton.addEventListener("click", function (e) {
			e.preventDefault();

			validation.validate().then(function (status) {
				if (status == "Valid") {
					submitButton.setAttribute("data-kt-indicator", "on");
					submitButton.disabled = true;

					var formData = new FormData(form);
					axios
						.post(baseUrl + "api/auth/login", formData)
						.then(function (response) {
							if (response.data.status == "success") {
								toastr.success(response.data.message, "Login Berhasil", {
									extendedTimeOut: 0,
									closeButton: false,
									closeDuration: 0,
								});
								window.location.href = response.data.data.redirect;
							} else {
								toastr.warning(response.data.message, "Gagal!", {
									timeOut: 5000,
									extendedTimeOut: 0,
									closeButton: false,
									closeDuration: 0,
								});
								submitButton.removeAttribute("data-kt-indicator");
								submitButton.disabled = false;
							}
						})
						.catch(function (error) {
							submitButton.removeAttribute("data-kt-indicator");
							submitButton.disabled = false;

							toastr.error(
								"System mengalami gangguan. Hubungi Administrator.",
								"Gagal!",
								{
									timeOut: 2000,
									extendedTimeOut: 0,
									closeButton: false,
									closeDuration: 0,
								}
							);
						});
				}
			});
		});
	};

	return {
		init: function () {
			form = document.querySelector("#kt_sign_in_form");
			submitButton = document.querySelector("#kt_sign_in_submit");

			handleValidation();
			handleSubmit();
		},
	};
})();
KTUtil.onDOMContentLoaded(function () {
	KTSigninGeneral.init();
});
