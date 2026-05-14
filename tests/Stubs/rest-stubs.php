<?php
/**
 * Minimal stubs of WP_REST_* classes for unit-testing REST controllers without WP.
 *
 * Only the surface the BASE Item List controllers touch is implemented. Brain Monkey
 * is preferred for plain WP functions, but REST classes are real PHP classes and
 * need to exist when the controllers' `use` statements resolve.
 */

if ( ! class_exists( 'WP_REST_Server' ) ) {
	class WP_REST_Server {
		const READABLE  = 'GET';
		const CREATABLE = 'POST';
	}
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
	class WP_REST_Request {

		private array $params      = array();
		private $json_params       = null;

		public function set_json_params( ?array $params ): self {
			$this->json_params = $params;
			return $this;
		}

		public function set_params( array $params ): self {
			$this->params = $params;
			return $this;
		}

		public function get_json_params() {
			return $this->json_params;
		}

		public function get_params(): array {
			return $this->params;
		}
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	class WP_REST_Response {

		/** @var mixed */
		public $data;

		public int $status;

		public function __construct( $data = null, int $status = 200 ) {
			$this->data   = $data;
			$this->status = $status;
		}

		public function get_data() {
			return $this->data;
		}

		public function get_status(): int {
			return $this->status;
		}
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {

		public string $code;
		public string $message;
		public array $data;

		public function __construct( string $code = '', string $message = '', array $data = array() ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}

		public function get_error_code(): string {
			return $this->code;
		}

		public function get_error_message(): string {
			return $this->message;
		}

		public function get_error_data() {
			return $this->data;
		}
	}
}
