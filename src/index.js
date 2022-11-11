/**
 * External dependencies
 */
import { addFilter } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import './index.scss';

const MyExamplePage = () => <h1>My Example Extension</h1>;

// addFilter( 'woocommerce_admin_pages_list', 'my-namespace', ( pages ) => {
// 	pages.push( {
// 		container: MyExamplePage,
// 		path: '/example',
// 		breadcrumbs: [ 'My Example Page' ],
// 		navArgs: {
// 			id: 'my-example-page',
// 		},
// 	} );

// 	return pages;
// } );
const addTableColumn = reportTableData => {
	if ('taxes' !== reportTableData.endpoint) {
		return reportTableData;
	}
console.log(reportTableData);
	const newHeaders = [
		...reportTableData.headers,
		{
			label: 'Net Sales',
			key: 'net_sale',
			required: false,
		}
	];

	const newRows = reportTableData.rows.map((row, index) => {
		
		const item = reportTableData.items.data[index];
		console.log(item.net_sale);
		

		const newRow = [
			...row,
		
			{
				display: item.net_sale,
				value: item.net_sale,
			},
		];
		
		return newRow;
	});
	const items_net_total = reportTableData.items.data.reduce(function (prev, item) {
        const net_sale = parseFloat(item.net_sale);
      return prev + net_sale;
}, 0);

	const newsummary = [
		...reportTableData.summary,
	     {
			 label: 'Total Net Sales',
			 value:'$'+ parseFloat(items_net_total).toFixed(2),
		},
	];
	reportTableData.headers = newHeaders;
	reportTableData.rows = newRows;
	reportTableData.summary = newsummary;
	return reportTableData;
};

addFilter('woocommerce_admin_report_table', 'reports', addTableColumn);