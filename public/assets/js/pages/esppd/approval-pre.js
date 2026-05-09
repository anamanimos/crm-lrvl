$(document).ready(function () {
	const id = $("#travel_request_id").val();
	const type = "pre";
	const employee_id = $("#employee_id").val();
	const apiUrl = "http://esppd.test/api/travel_request/approval";
	const redirectUrl = appUrl + "approval/pre";

	function confirmAndSend(action, notes, confirmText) {
		Swal.fire({
			title: "Apakah Anda yakin?",
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: confirmText,
		}).then((result) => {
			if (!result.isConfirmed) return;
			$.ajax({
				url: apiUrl,
				type: "POST",
				data: { id, employee_id, type, action, notes },
				success: function (response) {
					if (response && response.success) {
						if (action === "REJECTED") {
							$("#rejectModal").modal("hide");
						}
						Swal.fire({
							icon: "success",
							title: "Sukses",
							text:
								action === "APPROVED"
									? "Pengajuan disetujui"
									: "Pengajuan ditolak",
						});
						setTimeout(function () {
							window.location.href = redirectUrl;
						}, 1500);
					} else {
						Swal.fire({
							icon: "error",
							title: "Oops...",
							text: (response && response.message) || "Terjadi kesalahan",
						});
					}
				},
			});
		});
	}

	$("#btn-approve").on("click", function (e) {
		e.preventDefault();
		confirmAndSend("APPROVED", "", "Ya, setujui");
	});

	$("#btn-reject").on("click", function (e) {
		e.preventDefault();
		$("#rejectModal").modal("show");
	});

	$("#btn-reject-submit").on("click", function (e) {
		e.preventDefault();
		const reason = $("#reject_reason").val().trim();
		if (!reason) {
			Swal.fire({
				icon: "error",
				title: "Oops...",
				text: "Alasan wajib diisi",
			});
			return;
		}
		confirmAndSend("REJECTED", reason, "Ya, tolak");
	});
});
