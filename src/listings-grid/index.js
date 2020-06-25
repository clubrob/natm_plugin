import { registerBlockType } from "@wordpress/blocks";

import edit from './edit.js';

registerBlockType("natm/listings-grid", {
	title: "Listings Grid",
	icon: "store",
	category: "layout",
	attributes: {
		categories: {
			type: 'object'
		},
		selectedCategory: {
			type: 'string'
		}
	},
	edit
});
