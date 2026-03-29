<?php

namespace App\Exceptions;

use Exception;

/**
 * PSP env is missing or invalid for the chosen method; client should get 422, not ERROR logs.
 */
final class PaymentProviderNotConfiguredException extends Exception {}
