<?php declare(strict_types=1);

namespace Bogatyrev\validators;

use GuzzleHttp\Client;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Rules\AbstractRule;
use \Throwable;

abstract class BaseRule extends AbstractRule
{
    public function validate($input)
    {
        $result = $this->getData();
        if (empty($result)) {
            return false;
        }

        return \array_key_exists($input, $result);
    }

    protected function getData(): array
    {
        try {
            $client = new Client(['base_uri' => 'https://api.hostaway.com/']);
            $response = $client->request('GET', $this->getDataUri(), ['connect_timeout' => 5]);

            if ($response->getStatusCode() !== 200) {
                return false;
            }
            
            $responseData = \json_decode($response->getBody()->getContents(), true);
            $status = $responseData['status'] ?? false;
            if (! $status) {
                return false;
            }

            return $responseData['result'] ?? [];
        } catch (Throwable $t) {
            return [];
        }
    }

    abstract protected function getDataUri(): string;

    protected function createException()
    {
        return new ValidationException();
    }
}