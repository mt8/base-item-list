import { Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

function formatTimestamp( unixTime ) {
	if ( ! unixTime ) {
		return '';
	}
	try {
		return new Date( unixTime * 1000 ).toLocaleString();
	} catch ( e ) {
		return '';
	}
}

export default function ErrorLogPanel( { lastError, lastErrorTime } ) {
	if ( ! lastError ) {
		return (
			<p>
				{ __( '記録されているエラーはありません。', 'base-item-list' ) }
			</p>
		);
	}

	const occurredAt = formatTimestamp( lastErrorTime );

	return (
		<Notice status="warning" isDismissible={ false }>
			<p style={ { margin: 0 } }>
				<strong>{ __( '最後のエラー', 'base-item-list' ) }</strong>
				{ occurredAt && (
					<>
						{ ' ' }
						<span style={ { color: '#757575' } }>
							({ occurredAt })
						</span>
					</>
				) }
			</p>
			<code
				style={ {
					whiteSpace: 'pre-wrap',
					display: 'block',
					marginTop: 8,
				} }
			>
				{ lastError }
			</code>
		</Notice>
	);
}
