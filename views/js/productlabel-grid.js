const { $ } = window;

$(() => {
	const grid = new window.prestashop.component.Grid("product_label");

	grid.addExtension(
		new window.prestashop.component.GridExtensions.SortingExtension()
	);
	grid.addExtension(
		new window.prestashop.component.GridExtensions.SubmitRowActionExtension()
	);

	grid.addExtension(
		new window.prestashop.component.GridExtensions.FiltersResetExtension()
	);
});
