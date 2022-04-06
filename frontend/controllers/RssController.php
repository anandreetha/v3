<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class RssController extends Controller
{

    public function getrssvaluesAction()
    {
        // Try to get the RSS data from the cache first
        $rssData = $this->redisCache->get("rss-data");

        // Nothing in the cache
        if ($rssData === null) {
            // Initialise
            $count = 0;

            // Call the url for the RSS data
            //$feed = \Zend\Feed\Reader\Reader::import('http://www.bni.com/rss/');
			//$feed = \Zend\Feed\Reader\Reader::import('https://bnicom.bnitech.io/rss-bnic');
			$feed = \Zend\Feed\Reader\Reader::import('https://www.bni.com/rss-bnic');
			

            // Store
            $data = [
                'title' => $feed->getTitle(),
                'link' => $feed->getLink(),
                'dateModified' => $feed->getDateModified(),
                'description' => $feed->getDescription(),
                'language' => $feed->getLanguage(),
                'entries' => [],
            ];

            // Get the first 4 items
            foreach ($feed as $entry) {
                if ($count > 3) {
                    break;
                } else {
                    $edata = [
                        'title' => $entry->getTitle(),
                        'description' => $entry->getDescription(),
                        'dateModified' => $entry->getDateModified(),
                        'authors' => $entry->getAuthors(),
                        'link' => $entry->getLink(),
                        'content' => $entry->getContent(),
                        'image' => $this->getBlogPostImages($entry->getLink())
                    ];
                    $data['entries'][] = $edata;

                    $count++;
                }
            }

            // Cache filtered data for 1hr
            $this->redisCache->save("rss-data", $data, 3600);
        }

        // Get the data as it should now be in the cache
        $data = $this->redisCache->get("rss-data");

        $this->view->rssContent = $data["entries"];

        $response = new Response();
        $response->setHeader('Content-Type', 'application/json');

        if ($data["entries"] === null) {
            $response->setStatusCode(400, "Bad Request");
            $response->setContent(json_encode(array('error' => 'rss data should not be null')));
        } else {
            $response->setContent(json_encode($data["entries"]));
        }

        return $response;
    }

    private function getBlogPostImages($uri)
    {

        //Load uri:
        $info = Embed::create($uri);

        //Get all providers
        $providers = $info->getProviders();

        //Get the opengraph provider
        $opengraph = $providers['opengraph'];

        if ($opengraph->getBag()) {
            $images = $opengraph->getBag()->get('images');

            if (count($opengraph->getBag()->get('images'))) {
                return $images[0];
            }
        }

        //Return empty string if nothing is found
        return "";
    }
}
