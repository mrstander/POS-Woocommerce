/*global ajaxurl, jt_trader_import_params */
;(function ( $, window ) {

	/**
	 * productImportForm handles the import process.
	 */
	var jtTraderImport = function( $form, type ) {
		this.$form           = $form;
		this.xhr             = false;
		this.position        = 0;
		this.import_type 	 = type;

		// Number of import successes/failures.
		this.imported = 0;
		this.failed   = 0;
		this.updated  = 0;
		this.skipped  = 0;

		// Initial state.
		this.$form.find('.jt-trader-importer-progress').val( 0 );
		this.$form.find('.jt-trader-importer-form-start').hide();
		this.$form.find('.jt-trader-importer-form-progress').show();

		this.run_import = this.run_import.bind( this );

		// Start importing.
		this.run_import();
	};

	/**
	 * Run the import in batches until finished.
	 */
	jtTraderImport.prototype.run_import = function() {
		var $this = this;

		$.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'jt_trader_import',
				type: $this.import_type
			},
			dataType: 'json',
			success: function( response ) {
				if ( response.success ) {
					$this.$form.find('.jt-trader-importer-progress').val( response.data.percentage );

					if ( 0 >= response.data.remaining ) {
						$this.$form.find('.jt-trader-importer-progress').val( 0 );
						$this.$form.find('.jt-trader-importer-form-done').show();
						$this.$form.find('.jt-trader-importer-form-progress').hide();
						$this.$form.find('.jt-trader-importer-total').text(response.data.total);
						$this.$form.find('.jt-trader-importer-created').text(response.data.created);
						$this.$form.find('.jt-trader-importer-failed').text(response.data.failed);
						$this.$form.find('.jt-trader-importer-updated').text(response.data.updated);
						$this.$form.find('.jt-trader-importer-skipped').text(response.data.skipped);
						$this.$form.find('.button-jt-trader-import-details').click(function(e){
							e.preventDefault();
							window.location = response.data.url
						});
					} else {
						$this.run_import();
					}
				}
			}
		} ).fail( function( response ) {
			window.console.log( response );
		} );
	};

	/**
	 * Function to call productImportForm on jQuery selector.
	 */
	$.fn.jt_trader_importer = function(type) {
		new jtTraderImport( this, type );
		return this;
	};

	$(function() {
		$('#jt-trader-importer-product').on("submit", function(){
			$(this).jt_trader_importer('products');
			return false;
		});
		$('#jt-trader-importer-promotion').on("submit", function(){
			$(this).jt_trader_importer('promotions');
			return false;
		});
	});

})( jQuery, window );
