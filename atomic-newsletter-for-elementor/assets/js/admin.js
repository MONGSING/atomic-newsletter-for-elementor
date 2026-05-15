/* global alncAdmin */
( function () {
	'use strict';

	function alnc_update_delete_button() {
		var checked = document.querySelectorAll( 'input[name="alnc_subscriber_ids[]"]:checked' ).length;
		var btn     = document.getElementById( 'alnc-delete-selected-btn' );
		if ( btn ) {
			btn.style.display = checked > 0 ? 'inline-block' : 'none';
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		alnc_update_delete_button();

		// Select-all header checkbox.
		var selectAll = document.getElementById( 'alnc-select-all' );
		if ( selectAll ) {
			selectAll.addEventListener( 'change', function () {
				document.querySelectorAll( 'input[name="alnc_subscriber_ids[]"]' ).forEach( function ( c ) {
					c.checked = selectAll.checked;
				} );
				alnc_update_delete_button();
			} );
		}

		// Individual row checkboxes.
		document.querySelectorAll( 'input[name="alnc_subscriber_ids[]"]' ).forEach( function ( c ) {
			c.addEventListener( 'change', alnc_update_delete_button );
		} );

		// Delete Selected — confirm before submit.
		var deleteSelectedBtn = document.getElementById( 'alnc-delete-selected-btn' );
		if ( deleteSelectedBtn ) {
			deleteSelectedBtn.addEventListener( 'click', function ( e ) {
				if ( ! window.confirm( alncAdmin.confirmDeleteSelected ) ) {
					e.preventDefault();
				}
			} );
		}

		// Bulk Delete Filtered — confirm via data-confirm attribute.
		var bulkDeleteBtn = document.getElementById( 'alnc-bulk-delete-btn' );
		if ( bulkDeleteBtn ) {
			bulkDeleteBtn.addEventListener( 'click', function ( e ) {
				var message = this.getAttribute( 'data-confirm' ) || alncAdmin.confirmDeleteAll;
				if ( ! window.confirm( message ) ) {
					e.preventDefault();
				}
			} );
		}
	} );
}() );
