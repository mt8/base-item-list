import { fireEvent, render, screen } from '@testing-library/react';

import SettingsPanel, {
	hasRequiredCredentials,
} from '../components/SettingsPanel';

const baseSettings = {
	client_id: '',
	client_secret: '',
	callback_url: '',
	shop_url: '',
	use_default_css: false,
};

test( 'renders all input fields and the save button', () => {
	render(
		<SettingsPanel
			initialSettings={ baseSettings }
			callbackUrl="https://cb.example/"
			onSave={ () => {} }
			saving={ false }
		/>
	);
	expect( screen.getByLabelText( /client_id/ ) ).toBeInTheDocument();
	expect( screen.getByLabelText( /client_secret/ ) ).toBeInTheDocument();
	expect( screen.getByLabelText( /ショップ URL/ ) ).toBeInTheDocument();
	expect(
		screen.getByRole( 'button', { name: /設定を保存/ } )
	).toBeInTheDocument();
} );

test( 'submits the current values together with the injected callback URL', () => {
	const onSave = jest.fn();
	render(
		<SettingsPanel
			initialSettings={ { ...baseSettings, client_id: 'cid' } }
			callbackUrl="https://cb.example/"
			onSave={ onSave }
			saving={ false }
		/>
	);

	fireEvent.click( screen.getByRole( 'button', { name: /設定を保存/ } ) );

	expect( onSave ).toHaveBeenCalledTimes( 1 );
	expect( onSave ).toHaveBeenCalledWith(
		expect.objectContaining( {
			client_id: 'cid',
			callback_url: 'https://cb.example/',
		} )
	);
} );

test( 'hasRequiredCredentials reports false when any required field is empty', () => {
	expect( hasRequiredCredentials( null ) ).toBe( false );
	expect( hasRequiredCredentials( {} ) ).toBe( false );
	expect(
		hasRequiredCredentials( {
			client_id: 'a',
			client_secret: 'b',
			shop_url: '',
		} )
	).toBe( false );
} );

test( 'hasRequiredCredentials reports true once all required fields are non-empty', () => {
	expect(
		hasRequiredCredentials( {
			client_id: 'a',
			client_secret: 'b',
			shop_url: 'c',
		} )
	).toBe( true );
} );
