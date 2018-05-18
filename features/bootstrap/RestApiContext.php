<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behatch\Json\Json;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use \PHPUnit\Framework\Assert as Assertions;
use Symfony\Component\HttpFoundation\Request;
use Behatch\Json\JsonInspector;
use Behatch\Json\JsonSchema;

/**
 * Class RestApiContext
 */
class RestApiContext implements Context
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    private $authorization;

    /**
     * @var string
     */
    private $tokenExtractorHeaderName;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var \GuzzleHttp\Psr7\Request
     */
    private $request;

    /**
     * @var \GuzzleHttp\Psr7\Response
     */
    private $response;

    /**
     * @var array
     */
    private $placeHolders = array();

    /**
     * RestApiContext constructor.
     * @param ClientInterface   $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->tokenExtractorHeaderName = 'EngagedNation-Auth-Token';
    }

    /**
     * Adds Basic Authentication header to next request.
     *
     * @param string $username
     * @param string $password
     *
     * @Given /^I am authenticating as "([^"]*)" with "([^"]*)" password$/
     */
    public function iAmAuthenticatingAs(string $username, string $password)
    {
        $tokenExtractorHeaderName = (empty($this->tokenExtractorHeaderName))?'Authorization':$this->tokenExtractorHeaderName;
        $this->removeHeader($tokenExtractorHeaderName);
        $this->authorization = base64_encode($username . ':' . $password);
        $this->addHeader($tokenExtractorHeaderName, 'Bearer ' . $this->authorization);
    }

    /**
     * Adds JWT Token to Authentication header for next request
     *
     * @param string $username
     * @param string $password
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @Given /^I am successfully logged in with username: "([^"]*)", and password: "([^"]*)"$/
     */
    public function iAmSuccessfullyLoggedInWithUsernameAndPassword(string $username, string $password)
    {
        try {

            $this->iSendARequest('POST', 'login', [
                'json' => [
                    'username' => $username,
                    'password' => $password,
                ]
            ]);

            $this->theResponseCodeShouldBe('200');

            $tokenExtractorHeaderName = (empty($this->tokenExtractorHeaderName))?'Authorization':$this->tokenExtractorHeaderName;
            $tokenValuePrefix = ($tokenExtractorHeaderName==='Authorization')?'Bearer ':'';

            $responseBody = json_decode((string)$this->response->getBody(), true);
            $this->addHeader($tokenExtractorHeaderName, $tokenValuePrefix.$responseBody['data']['token']);

        } catch (RequestException $e) {

            echo Psr7\str($e->getRequest());

            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }

        }
    }

    /**
     * @When I have forgotten to set the :header
     */
    public function iHaveForgottenToSetThe($header)
    {
        $this->addHeader($header, '');
    }

    /**
     * Sets a HTTP Header.
     *
     * @param string $name  header name
     * @param string $value header value
     *
     * @Given /^I set header "([^"]*)" with value "([^"]*)"$/
     */
    public function iSetHeaderWithValue($name, $value)
    {
        $this->addHeader($name, $value);
    }

    /**
     * Sends HTTP request to specific relative URL.
     *
     * @param string $method request method
     * @param string $url    relative url
     * @param array $data
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     *
     * @When /^(?:I )?send a "([A-Z]+)" request to "([^"]+)"$/
     *
     */
    public function iSendARequest($method, $url, array $data = [])
    {
        $url = $this->prepareUrl($url);
        $data = $this->prepareData($data);

        try { // Comment the try block to debug the Request
            $this->response = $this->getClient()->request($method, $url, $data);
        } catch (RequestException $e) {
            var_dump($e->getResponse()->getHeader('x-debug-token-link'));
            if ($e->hasResponse()) {
                $this->response = $e->getResponse();
            }
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    /**
     * Sends HTTP request to specific URL with field values from Table.
     *
     * @param string    $method request method
     * @param string    $url    relative url
     * @param TableNode $post   table of post values
     * @throws Exception
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with values:$/
     */
    public function iSendARequestWithValues(string $method, string $url, TableNode $post)
    {
        $url = $this->prepareUrl($url);
        $fields = array();

        foreach ($post->getRowsHash() as $key => $val) {
            $fields[$key] = $this->replacePlaceHolder($val);
        }

        $bodyOption = array(
            'body' => json_encode($fields),
        );
        $this->request = $this->getClient()->createRequest($method, $url, $bodyOption);
        if (!empty($this->headers)) {
            $this->request->addHeaders($this->headers);
        }

        $this->sendRequest();
    }

    /**
     * Sends HTTP request to specific URL with raw body from PyString.
     *
     * @param string       $method request method
     * @param string       $url    relative url
     * @param PyStringNode $string request body
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     *
     * @When /^(?:I )?send a "([A-Z]+)" request to "([^"]+)" with body:$/
     */
    public function iSendARequestWithBody(string $method, string $url, string $string)
    {
        $url = $this->prepareUrl($url);
        $string = $this->replacePlaceHolder(trim($string));

        $this->request = $this->iSendARequest(
            $method,
            $url,
            [ 'body' => $string, ]
        );
    }

    /**
     * Sends HTTP request to specific URL with form data from PyString.
     *
     * @param string       $method request method
     * @param string       $url    relative url
     * @param PyStringNode $body   request body
     * @throws Exception
     *
     * @When /^(?:I )?send a "([A-Z]+)" request to "([^"]+)" with form data:$/
     */
    public function iSendARequestWithFormData(string $method, string $url, PyStringNode $body)
    {
        $url = $this->prepareUrl($url);
        $body = $this->replacePlaceHolder(trim($body));

        $fields = array();
        parse_str(implode('&', explode("\n", $body)), $fields);
        $this->request = $this->getClient()->createRequest($method, $url);
        /** @var \GuzzleHttp\Post\PostBodyInterface $requestBody */
        $requestBody = $this->request->getBody();
        foreach ($fields as $key => $value) {
            $requestBody->setField($key, $value);
        }

        $this->sendRequest();
    }

    /**
     * @When /^(?:I )?send a multipart "([A-Z]+)" request to "([^"]+)" with form data:$/
     *
     * @param string $method
     * @param string $url
     * @param TableNode $post
     */
    public function iSendAMultipartRequestToWithFormData(string $method, string $url, TableNode $post)
    {
        $url = $this->prepareUrl($url);

        $this->request = $this->getClient()->createRequest($method, $url);

        $data = $post->getColumnsHash()[0];

        $hasFile = false;

        if (array_key_exists('filePath', $data)) {
            $filePath = $this->dummyDataPath . $data['filePath'];
            unset($data['filePath']);
            $hasFile = true;
        }


        /** @var \GuzzleHttp\Post\PostBodyInterface $requestBody */
        $requestBody = $this->request->getBody();
        foreach ($data as $key => $value) {
            $requestBody->setField($key, $value);
        }


        if ($hasFile) {
            $file = fopen($filePath, 'rb');
            $postFile = new PostFile('uploadedFile', $file);
            $requestBody->addFile($postFile);
        }


        if (!empty($this->headers)) {
            $this->request->addHeaders($this->headers);
        }
        $this->request->setHeader('Content-Type', 'multipart/form-data');

        $this->sendRequest();
    }

    /**
     * Checks that response has specific status code.
     *
     * @Then the response code should be :code
     *
     * @param string $code
     * @throws \Exception
     */
    public function theResponseCodeShouldBe(string $code)
    {
        $expected = intval($code);
        $actual = intval($this->response->getStatusCode());

        if ($expected !== $actual) {
            $json = new Json($this->response->getBody());
            $inspector = new JsonInspector('javascript');
            $message = $inspector->evaluate($json, 'message');
            throw new \RuntimeException(
                "Failed asserting that $actual (Actual) is identical to $expected (Expected). ".$message
            );
        }
    }

    /**
     * Checks that response body contains specific text.
     *
     * @param string $text
     *
     * @Then /^(?:the )?response should contain "((?:[^"]|\\")*)"$/
     */
    public function theResponseShouldContain(string $text)
    {
        $expectedRegexp = '/' . preg_quote($text) . '/i';
        $actual = (string) $this->response->getBody();
        Assertions::assertRegExp($expectedRegexp, $actual);
    }

    /**
     * Checks that response body doesn't contains specific text.
     *
     * @param string $text
     *
     * @Then /^(?:the )?response should not contain "([^"]*)"$/
     */
    public function theResponseShouldNotContain(string $text)
    {
        $expectedRegexp = '/' . preg_quote($text) . '/';
        $actual = (string) $this->response->getBody();
        Assertions::assertNotRegExp($expectedRegexp, $actual);
    }

    /**
     * The response should contain the properties and values.
     *
     * @param TableNode $properties A list of properties and values.
     *
     * @throws \RuntimeException
     *
     * @Then /^(?:the )?response should contain the properties and values:$/
     */
    public function theResponseShouldContainPropertiesAndValues(TableNode $properties)
    {
        $response = json_decode((string)$this->response->getBody(), true);

        if (!is_array($response)) {
            throw new \RuntimeException('The response is not valid json.');
        }

        foreach ($properties->getColumnsHash()[0] as $propertyName => $propertyValue) {

            Assertions::assertTrue(isset($response[$propertyName]), "Property missing from response: ${propertyName}");

            if ($propertyValue == '*') {
                // propertyValue is wildcard and is accepted as anything.
                continue;
            }

            $decodedValue = json_decode($propertyValue, true);
            if ($decodedValue !== false) {
                $propertyValue = $decodedValue;
            }

            if (is_array($propertyValue)) {
                $propertyValue = $this->setWildCard($propertyValue, $response[$propertyName]);
            }

            Assertions::assertEquals($response[$propertyName], $propertyValue);
        }
    }

    /**
     * Checks that response body contains JSON from PyString.
     *
     * Do not check that the response body /only/ contains the JSON from PyString,
     *
     * @param PyStringNode $jsonString
     *
     * @throws \RuntimeException
     * @throws Exception
     *
     * @Then /^(?:the )?response should contain json:$/
     */
    public function theResponseShouldContainJson(PyStringNode $jsonString)
    {
        $etalon = json_decode($this->replacePlaceHolder($jsonString->getRaw()), true);
        $actual = json_decode($this->response->getBody(), true);

        if (null === $etalon) {
            throw new \RuntimeException(
                "Can not convert etalon to json:\n" . $this->replacePlaceHolder($jsonString->getRaw())
            );
        }

        Assertions::assertGreaterThanOrEqual(count($etalon), count($actual));
        foreach ($etalon as $key => $needle) {
            Assertions::assertArrayHasKey($key, $actual);
            Assertions::assertEquals($etalon[$key], $actual[$key]);
        }
    }

    /**
     * @When the response should contains a valid JSON
     */
    public function theResponseShouldContainsAValidJson()
    {
        $response = json_decode((string) $this->response->getBody(), true);
        if(json_last_error() != JSON_ERROR_NONE) {
            var_dump($response);
            throw new \Exception('The response doesn\'t have a valid JSON format.');
        }
    }

    /**
     * @When the JSON node :node should be an array with :quantity elements
     */
    public function theJsonNodeShouldBeAnArrayWithElements(string $node, int $quantity)
    {
        try {

            $json = new Json($this->response->getBody());

            $inspector = new JsonInspector('javascript');
            $actual = $inspector->evaluate($json, $node);

            if(!is_array($actual)) {
                throw new \Exception("This node \"$node\" is not an array.");
            }

            if(count($actual)!== $quantity) {
                throw new \Exception("This node \"$node\" doesn't have the elements specified (".count($actual).").");
            }

        }
        catch (\Exception $e){
            var_dump($e->getMessage());
        }


    }

    /**
     * Prints last response body.
     *
     * @Then print response
     */
    public function printResponse()
    {
        $response = $this->response;

        echo sprintf(
            "%d:\n%s",
            $response->getStatusCode(),
            $response->getBody()
        );
    }

    /**
     * @Then the response header :header should be equal to :value
     */
    public function theResponseHeaderShouldBeEqualTo(string $header, string $value)
    {
        $header = $this->response->getHeaders()[$header];
        Assertions::assertContains($value, $header);
    }

    /**
     * Prepare URL by replacing placeholders and trimming slashes.
     *
     * @param string $url
     * @throws Exception
     *
     * @return string
     */
    private function prepareUrl(string $url)
    {
        return ltrim($this->replacePlaceHolder($url), '/');
    }

    /**
     * Sets place holder for replacement.
     *
     * you can specify placeholders, which will
     * be replaced in URL, request or response body.
     *
     * @param string $key   token name
     * @param string $value replace value
     */
    public function setPlaceHolder(string $key, string $value)
    {
        $this->placeHolders[$key] = $value;
    }

    /**
     * @Then I follow the link in the Location response header
     */
    public function iFollowTheLinkInTheLocationResponseHeader()
    {
        $location = $this->response->getHeader('Location')[0];

        $this->iSendARequest(Request::METHOD_GET, $location);
    }

    /**
     * @Then the JSON should be valid according to this schema:
     */
    public function theJsonShouldBeValidAccordingToThisSchema(PyStringNode $schema)
    {
        $inspector = new JsonInspector('javascript');

        $json = new Json(json_encode($this->response->json()));

        $inspector->validate(
            $json,
            new JsonSchema($schema)
        );
    }

    /**
     * Checks, that given JSON node is equal to given value
     *
     * @Then the JSON node :node should be equal to :text
     *
     * @param string $node
     * @param string $text
     * @throws \Exception
     */
    public function theJsonNodeShouldBeEqualTo(string $node, string $text)
    {

        $json = new Json($this->response->getBody());

        $inspector = new JsonInspector('javascript');
        $actual = $inspector->evaluate($json, $node);

        if ($actual != $text) {
            throw new \Exception(
                sprintf("The node value is '%s'", json_encode($actual))
            );
        }
    }

    /**
     * Checks, that given JSON node is true
     *
     * @Then the JSON node :node should be true
     */
    public function theJsonNodeShouldBeTrue($node)
    {
        $json = new Json($this->response->getBody());

        $inspector = new JsonInspector('javascript');
        try {
            $actual = $inspector->evaluate($json, $node);
        }
        catch (\Exception $exception) {
            var_dump($exception->getMessage());
            var_dump($json->getContent());
        }

        if (true !== $actual) {
            throw new \Exception(
                sprintf('The node value is `%s`', json_encode($actual))
            );
        }
    }

    /**
     * Checks, that given JSON node is false
     *
     * @Then the JSON node :node should be false
     *
     * @param $node
     * @throws \Exception
     */
    public function theJsonNodeShouldBeFalse($node)
    {
        $json = new Json($this->response->getBody());

        $inspector = new JsonInspector('javascript');
        try {
            $actual = $inspector->evaluate($json, $node);
        }
        catch (\Exception $exception) {
            var_dump($exception->getMessage());
            var_dump($json->getContent());
        }

        if (false !== $actual) {
            throw new \Exception(
                sprintf('The node value is `%s`', json_encode($actual))
            );
        }
    }

    /**
     * Checks, that given JSON node is not null.
     *
     * @Then the JSON node :node should not be null
     *
     * @param $node
     * @throws \Exception
     */
    public function theJsonNodeShouldNotBeNull($node)
    {
        $json = new Json($this->response->getBody());

        $inspector = new JsonInspector('javascript');
        try {
            $actual = $inspector->evaluate($json, $node);
        }
        catch (\Exception $exception) {
            var_dump($exception->getMessage());
            var_dump($json->getContent());
        }

        if (is_null($actual)) {
            throw new \Exception(
                sprintf('The node value is `%s`', json_encode($actual))
            );
        }
    }

    /**
     * Replaces placeholders in provided text.
     *
     * @param string $string
     *
     * @return string
     * @throws \Exception
     */
    protected function replacePlaceHolder($string)
    {
        foreach ($this->placeHolders as $key => $val) {
            $string = str_replace($key, $val, $string);
        }

        return $string;
    }

    /**
     * Returns headers, that will be used to send requests.
     *
     * @return array
     */
    protected function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Adds header
     *
     * @param string $name
     * @param string $value
     */
    protected function addHeader(string $name, string $value)
    {
        if ( ! isset($this->headers[$name])) {
            $this->headers[$name] = $value;
        }

        if (!is_array($this->headers[$name])) {
            $this->headers[$name] = [$this->headers[$name]];
        }

        $this->headers[$name] = $value;
    }

    /**
     * Removes a header identified by $headerName
     *
     * @param string $headerName
     */
    protected function removeHeader(string $headerName)
    {
        if (array_key_exists($headerName, $this->headers)) {
            unset($this->headers[$headerName]);
        }
    }

    /**
     * @return ClientInterface
     */
    private function getClient()
    {
        if (null === $this->client) {
            throw new \RuntimeException('Client has not been set in WebApiContext');
        }

        return $this->client;
    }

    /**
     * @param mixed $data
     * @return array
     */
    private function prepareData($data): array
    {
        if (!empty($this->headers)) {
            $data = array_replace(
                $data,
                ["headers" => $this->headers]
            );
        }

        return $data;
    }

    /**
     * Sets wild card values based on a template array.
     *
     * @param array $rebase The array to update the wild card values of.
     * @param array $template The array to use as the templatefor wildcard characters.
     *
     * @return array The updated array.
     */
    private function setWildCard(array $rebase, array $template)
    {
        foreach($rebase as $key => $value) {
            if (!isset($template[$key])) continue;

            if ($value == '*') {
                $rebase[$key] = $template[$key];
                continue;
            }

            if (is_array($value)) {
                $rebase[$key] = $this->setWildCard($value, $template[$key]);
            }
        }

        return $rebase;
    }

}
