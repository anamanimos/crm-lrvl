"use strict";

$(document).ready(function () {
	// on submit
	$("#form-bluk-update").on("click", function (e) {
		e.preventDefault();
		// get form data
		let groups = [];
		const $inputs = $('input[name="group_id[]"]');
		if ($inputs.length === 1) {
			const raw = $inputs.val();
			try {
				const parsed = JSON.parse(raw);
				groups = Array.isArray(parsed) ? parsed : [];
			} catch (err) {
				groups = (raw || "")
					.split(",")
					.map((v) => v.trim())
					.filter((v) => v.length > 0);
			}
		} else {
			groups = $inputs
				.map((_, el) => el.value)
				.get()
				.filter((v) => v && v.length > 0);
		}

		const transportData = groups.map((group) => {
			const $fixed = $(`input[name='fixed_amount_${group}']`);
			const $max = $(`input[name='maximum_amount_${group}']`);
			const toNumber = (v) => {
				const n = Number(v);
				return Number.isFinite(n) ? n : 0;
			};
			return {
				group_id: group,
				id: $fixed.data("transport-id") ?? $max.data("transport-id") ?? null,
				transport_type:
					$fixed.data("transport-type") ?? $max.data("transport-type") ?? null,
				old_fixed_amount: toNumber($fixed.data("old-value")),
				old_maximum_amount: toNumber($max.data("old-value")),
				new_fixed_amount: toNumber($fixed.val()),
				new_maximum_amount: toNumber($max.val()),
			};
		});

		// ajax
		$.ajax({
			url: appUrl + "master/transport/ajax/bulk-update",
			type: "POST",
			data: {
				transport_data: JSON.stringify(transportData),
			},
			success: function (response) {
				// success
				if (response.success) {
					// show success message
					Swal.fire({
						icon: "success",
						title: "Sukses",
						text: `${response.updated} data transportasi diupdate`,
					}).then(() => {
						// reload table
						window.location.reload();
					});
				} else {
					// show error message
					Swal.fire({
						icon: "error",
						title: "Gagal",
						text: "Tidak ada data yang diupdate",
					});
				}
				console.log(response);
			},
			error: function (response) {
				// error
				console.log(response);
			},
		});
	});
});
