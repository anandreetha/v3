<?php

namespace Multiple\Core\Widgets\Widget\Country;

class PodcastWidget extends BaseWidget
{
    private $podcastUrl = "https://www.bnipodcast.com/";
    private $podcastCustomImageUrl = "";
	private $podcastTitle="Podcast";

    public function getContent()
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/');

        $this->view->podcastURL = $this->getPodcastUrl();

        if ($this->isRenderStaticContent()) {
            $this->view->podcastCustomImageUrl = $this->convertWidgetImagePathToStaticUrl(
                $this->getPodcastCustomImageUrl()
            );
        } else {
            $this->view->podcastCustomImageUrl = $this->getPodcastCustomImageUrl();
        }
		$this->view->podcastTitle = $this->getPodcastTitle();
		$this->view->podcastAlt = $this->getPodcastAlt();
        // Render a view and return its contents as a string
        return $this->view->render('podcast-widget');
    }

    /**
     * @return string
     */
    public function getPodcastUrl(): string
    {
        return $this->podcastUrl;
    }

    /**
     * @param string $podcastUrl
     */
    public function setPodcastUrl(string $podcastUrl): void
    {
        $this->podcastUrl = $podcastUrl;
    }

    /**
     * @return string
     */
    public function getPodcastCustomImageUrl(): string
    {
        return $this->podcastCustomImageUrl;
    }

    /**
     * @param string $podcastTitle
     */
    public function setPodcastCustomImageUrl(string $podcastCustomImageUrl): void
    {
        $this->podcastCustomImageUrl = $podcastCustomImageUrl;
    }
	public function getPodcastTitle(): string
    {
        return $this->podcastTitle;
    }
	public function setPodcastTitle(string $podcastTitle): void
    {
		$this->podcastTitle = $podcastTitle;
    }
	public function getPodcastAlt(): string
    {
        return $this->podcastAlt;
    }
	public function setPodcastAlt(string $podcastAlt): void
    {
		$this->podcastAlt = $podcastAlt;
    }
}



