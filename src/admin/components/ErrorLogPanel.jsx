import { Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function ErrorLogPanel( { lastError } ) {
	if ( ! lastError ) {
		return null;
	}

	return (
		<Notice status="warning" isDismissible={ false }>
			<strong>{ __( '最後のエラー', 'base-item-list' ) }: </strong>
			<code style={ { whiteSpace: 'pre-wrap' } }>{ lastError }</code>
		</Notice>
	);
}
