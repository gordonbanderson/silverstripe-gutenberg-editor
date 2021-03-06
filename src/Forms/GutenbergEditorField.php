<?php

namespace MadeHQ\Gutenberg\Forms;

use SilverStripe\Forms\TextareaField;
use SilverStripe\Control\{HTTPRequest, HTTPResponse_Exception, HTTPResponse};
use SilverStripe\Core\Convert;
use SilverStripe\View\Requirements;

use Embed\Embed;
use Embed\Adapters\Webpage;
use Embed\Http\CurlDispatcher;

class GutenbergEditorField extends TextareaField
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'oembed',
    ];

    /**
     * @config
     * @var array
     */
    private static $oembed_options = [
        'min_image_width' => 60,
        'min_image_height' => 60,
        'html' => [
            'max_images' => 10,
            'external_images' => false,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function Field($properties = array())
    {
        $config = Convert::array2json([
            'oembed' => $this->Link('oembed'),
            'personalisation' => [
                [
                    'label' => 'Something',
                    'value' => 'Something',
                ],
                [
                    'label' => 'Something Else',
                    'value' => 'Something Else',
                ],
            ],
        ]);

        Requirements::insertHeadTags(
            sprintf('<script>window.gutenbergConfig = %s;</script>', $config)
        );

        return parent::Field($properties);
    }

    /**
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function oembed(HTTPRequest $request)
    {
        // Grab the URL
        $url = $request->getVar('url');

        if (is_null($url) || !strlen($url)) {
            return new HTTPResponse_Exception();
        }

        try {
            // Embed options
            $options = array_merge(
                Embed::$default_config, static::$oembed_options
            );

            // Useful if we ever wish to find out why fetch went wrong
            $dispatcher = new CurlDispatcher();

            // Try to fetch data
            $webpage = Embed::create($url, $options, $dispatcher);

            // Get all providers
            $providers = $webpage->getProviders();

            if (array_key_exists('oembed', $providers)) {
                $data = $providers['oembed']->getBag()->getAll();
            } else {
                $data = null;
            }
        } catch (Exception $exception) {
            // Don't care about errors
            $data = null;
        }

        // Body & status code
        $responseBody = Convert::array2json($data);
        $statusCode = is_null($data) ? 404 : 200;

        // Get a new response going
        $response = new HTTPResponse($responseBody, $statusCode);

        // Add some headers
        $response->addHeader('Content-Type', 'application/json; charset=utf-8');
        $response->addHeader('Access-Control-Allow-Methods', 'GET');
        $response->addHeader('Access-Control-Allow-Origin', '*');

        // Return
        return $response;
    }
}
