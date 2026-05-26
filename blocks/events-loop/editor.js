( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;
	var TextControl = wp.components.TextControl;
	var ToggleControl = wp.components.ToggleControl;
	var SelectControl = wp.components.SelectControl;
	var __ = wp.i18n.__;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;

	registerBlockType( 'awesome-events/events-loop', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( {
				className: 'awesome-events-loop-editor',
			} );

			var cityOptions = [ { label: __( 'All cities', 'awesome-events' ), value: '' } ];
			if ( window.awesomeEventsBlockData && window.awesomeEventsBlockData.cities ) {
				window.awesomeEventsBlockData.cities.forEach( function ( term ) {
					cityOptions.push( { label: term.name, value: term.slug } );
				} );
			}

			var typeOptions = [ { label: __( 'All types', 'awesome-events' ), value: '' } ];
			if ( window.awesomeEventsBlockData && window.awesomeEventsBlockData.types ) {
				window.awesomeEventsBlockData.types.forEach( function ( term ) {
					typeOptions.push( { label: term.name, value: term.slug } );
				} );
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Events Loop', 'awesome-events' ), initialOpen: true },
						el( RangeControl, {
							label: __( 'Number of events', 'awesome-events' ),
							value: attributes.count,
							onChange: function ( value ) {
								setAttributes( { count: value } );
							},
							min: 1,
							max: 24,
						} ),
						el( TextControl, {
							label: __( 'Heading (optional)', 'awesome-events' ),
							value: attributes.heading,
							onChange: function ( value ) {
								setAttributes( { heading: value } );
							},
						} ),
						el( SelectControl, {
							label: __( 'City', 'awesome-events' ),
							value: attributes.eventCity,
							options: cityOptions,
							onChange: function ( value ) {
								setAttributes( { eventCity: value } );
							},
						} ),
						el( SelectControl, {
							label: __( 'Event type', 'awesome-events' ),
							value: attributes.eventType,
							options: typeOptions,
							onChange: function ( value ) {
								setAttributes( { eventType: value } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'Show link to all events', 'awesome-events' ),
							checked: attributes.showArchiveLink,
							onChange: function ( value ) {
								setAttributes( { showArchiveLink: value } );
							},
						} ),
						attributes.showArchiveLink
							? el( TextControl, {
									label: __( 'Link label', 'awesome-events' ),
									value: attributes.archiveLinkLabel,
									onChange: function ( value ) {
										setAttributes( { archiveLinkLabel: value } );
									},
									help: __(
										'Leave empty for “View all events”.',
										'awesome-events'
									),
							  } )
							: null
					)
				),
				el(
					'div',
					blockProps,
					el(
						'p',
						{ className: 'awesome-events-loop-editor__label' },
						el( 'strong', null, __( 'Events Loop', 'awesome-events' ) ),
						' — ',
						__( 'Upcoming events:', 'awesome-events' ),
						' ',
						attributes.count
					),
					attributes.heading
						? el( 'p', null, attributes.heading )
						: null
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
