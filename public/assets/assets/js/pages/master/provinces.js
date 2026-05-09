"use strict";

$(document).ready(function () {
	var table = $("#kt_table_provinces").DataTable({
		lengthChange: false,
		searching: true,
		ordering: true,
		info: true,
		autoWidth: false,
		responsive: true,
		dom: "lrtip", // Hide default search box
	});

	$('[data-kt-user-table-filter="search"]').on("keyup", function () {
		table.search(this.value).draw();
	});

	// 1. Inisialisasi Select2 (tanpa data dulu)
	var $selectFilter = $("#filter_division_position");

	$selectFilter.select2({
		placeholder: "Pilih divisi / posisi",
		allowClear: true,
		width: "100%",
		dropdownParent: $("#modal_filter"),
	});

	// 2. Load JSON dan isi option
	loadDivisionPositionJSON($selectFilter);

	// 3. Pasang event bertingkat (divisi/posisi)
	setupDivisionPositionEvents($selectFilter);

	// 4. Tombol reset filter
	$("#btn_reset_filter").on("click", function () {
		$selectFilter.val(null).trigger("change");
	});

	// 5. (Opsional) log saat submit
	$("#form_filter_employee").on("submit", function () {
		console.log("Filter dipilih:", $selectFilter.val());
	});

	// --- fungsi load JSON ---
	// --- fungsi load JSON ---
	function loadDivisionPositionJSON($select) {
		var jsonUrl = "/json/division_position.json"; // path kamu sudah benar

		$.getJSON(jsonUrl, function (data) {
			console.log("JSON loaded:", data);

			// Bersihkan pilihan lama
			$select.empty();

			// Normalisasi struktur: data bisa berupa array langsung
			var divisions = [];

			if (Array.isArray(data)) {
				// Kasus kamu: root = array
				divisions = data;
			} else if (Array.isArray(data.divisions)) {
				// Kasus lain: { divisions: [...] }
				divisions = data.divisions;
			} else {
				console.log("Struktur JSON tidak sesuai yang diharapkan:", data);
				return;
			}

			// Baca data-selected dari attribute (untuk preselect)
			var preselected = $select.data("selected") || [];

			// Pastikan preselected selalu array
			if (typeof preselected === "string") {
				try {
					preselected = JSON.parse(preselected);
				} catch (e) {
					preselected = [];
				}
			}

			// Loop tiap divisi
			divisions.forEach(function (div) {
				// Safety check sedikit
				if (!div || !div.id || !div.name) return;

				// Option divisi
				var $optDiv = $("<option>")
					.val(div.id)
					.text(div.name)
					.attr("data-type", "division");

				$select.append($optDiv);

				// Loop posisi
				if (Array.isArray(div.positions)) {
					div.positions.forEach(function (pos) {
						if (!pos || !pos.id || !pos.name) return;

						var $optPos = $("<option>")
							.val(pos.id)
							.text("- " + pos.name)
							.attr("data-type", "position")
							.attr("data-parent", div.id);

						$select.append($optPos);
					});
				}
			});

			// Set preselected kalau ada
			if (preselected && preselected.length) {
				$select.val(preselected);
			}

			// Trigger change agar Select2 update
			$select.trigger("change");
		}).fail(function (jqXHR, textStatus, errorThrown) {
			console.error("Gagal load JSON:", textStatus, errorThrown);
		});
	}

	// --- fungsi event bertingkat ---
	function setupDivisionPositionEvents($select) {
		$select.on("select2:select", function (e) {
			var data = e.params.data;
			var $opt = $(data.element);

			// Jika pilih DIVISI
			if ($opt.data("type") === "division") {
				var divisiId = $opt.val();

				// Disable semua posisi anak
				$select
					.find('option[data-parent="' + divisiId + '"]')
					.prop("disabled", true)
					.prop("selected", false);

				$select.trigger("change.select2");
			}

			// Jika pilih POSISI
			if ($opt.data("type") === "position") {
				var parentId = $opt.data("parent");

				// Optional: disable parent-nya
				$select
					.find('option[value="' + parentId + '"]')
					.prop("disabled", true)
					.prop("selected", false);

				$select.trigger("change.select2");
			}
		});

		$select.on("select2:unselect", function (e) {
			var data = e.params.data;
			var $opt = $(data.element);

			// Jika DIVISI di-unselect: enable lagi posisi anak
			if ($opt.data("type") === "division") {
				var divisiId = $opt.val();

				$select
					.find('option[data-parent="' + divisiId + '"]')
					.prop("disabled", false);

				$select.trigger("change.select2");
			}

			// Jika POSISI di-unselect: optional enable lagi parent
			if ($opt.data("type") === "position") {
				var parentId = $opt.data("parent");

				$select
					.find('option[value="' + parentId + '"]')
					.prop("disabled", false);

				$select.trigger("change.select2");
			}
		});
	}
});
