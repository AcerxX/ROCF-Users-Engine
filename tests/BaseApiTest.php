<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseApiTest extends WebTestCase
{
    public const REPETITIVE_ARRAY_FLAG = 'REPETITIVE-KEY-CHECK';
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    public function testDummy()
    {
        static::assertEquals(true, true);
    }

    /**
     * @param string $route
     * @param string $method
     * @param array $parameters
     * @return array
     */
    protected function getApiResponse(string $route, string $method = self::METHOD_GET, array $parameters = []): array
    {
        $client = static::createClient();
        $client->request(
            $method,
            $route,
            $parameters,
            [],
            [
                'HTTP_CUSTOMER_ID' => 82,
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        if ($client->getResponse() === null) {
            static::fail('Response should not be null!');
        }

        if (empty($client->getResponse()->getContent())) {
            static::fail('Response should not be empty!');
        }

        static::assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));
        static::assertEquals(200, $client->getResponse()->getStatusCode());

        return json_decode($client->getResponse()->getContent(), true);
    }

    /**
     * @param array $response
     * @param array $keysToTest
     * @param bool $shouldBeError
     */
    protected function checkResponse(array $response, array $keysToTest, bool $shouldBeError = false)
    {
        $this->checkErrorMessage($response, $shouldBeError);
        $this->checkSameKeys($response, $keysToTest);
        $this->checkValues($response, $keysToTest);
    }

    /**
     * @param array $response
     * @param bool $shouldBeError
     */
    private function checkErrorMessage(array $response, bool $shouldBeError): void
    {
        if (array_key_exists('isError', $response) && $response['isError'] !== $shouldBeError) {
            $errorMessage = $response['errorMessage'] ?? '<<< error message not found by automatic tests >>>';
            static::fail("Error message received: $errorMessage");
        }
    }

    /**
     * @param array $response
     * @param array $keysToTest
     */
    private function checkSameKeys(array $response, array $keysToTest): void
    {
        if (count($diffs = array_diff_key($response, $keysToTest)) > 0) {
            $errorMessage = 'Fail asserting that the TEST KEYS array contain the following keys from the response: ' .
                implode(', ', array_keys($diffs));

            if (\array_key_exists(0, $diffs)) {
                $errorMessage .= ".\n\nMaybe you forgot to wrap an array with the REPETITIVE_ARRAY_FLAG?";
                $errorMessage .= "\n\nIf this is the case, "
                    . "the array that should be wrapped is containing the following keys:\n" .
                    implode("\n", array_keys(reset($response)));
            }

            static::fail($errorMessage);
        }
        if (count($diffs = array_diff_key($keysToTest, $response)) > 0) {
            static::fail(
                'Fail asserting that the RESPONSE contain the following keys from the test keys array: ' .
                implode(', ', array_keys($diffs))
            );
        }
    }

    /**
     * @param array $response
     * @param array $keysToTest
     */
    private function checkValues(array $response, array $keysToTest): void
    {
        foreach ($response as $key => $value) {
            if ($keysToTest[$key] === false) {
                continue;
            }

            if (\is_array($value)) {
                if (array_key_exists(self::REPETITIVE_ARRAY_FLAG, $keysToTest[$key])) {
                    foreach ($value as $repetitiveItem) {
                        $this->checkResponse($repetitiveItem, $keysToTest[$key][self::REPETITIVE_ARRAY_FLAG]);
                    }
                } else {
                    $this->checkResponse($value, $keysToTest[$key]);
                }
            } else {
                if ($value === '' || $value === null) {
                    static::fail("The value of key '$key' should not be empty!");
                }
            }
        }
    }
}
