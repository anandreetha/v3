<?php

namespace Multiple\Core\Widgets\Widget\Country;

class SuccessNetWidget extends BaseWidget
{
    private $foundationUrl= "http://www.bnifoundation.org/";
    private $foundationCustomImageUrl= "";
	private $foundationTitle= "Foundation";

    public function getContent()
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/');
        $this->view->foundationUrl = $this->getFoundationUrl();

        if ($this->isRenderStaticContent()) {
            $this->view->foundationCustomImageUrl = $this->convertWidgetImagePathToStaticUrl(
                $this->getFoundationCustomImageUrl()
            );
        } else {
            $this->view->foundationCustomImageUrl = $this->getFoundationCustomImageUrl();
        }

		$this->view->foundationTitle = $this->getFoundationTitle();
		$this->view->foundationAlt = $this->getFoundationAlt();
        // Render a view and return its contents as a string
        return $this->view->render('success-net-widget');
    }

    /**
     * @return string
     */
    public function getFoundationUrl(): string
    {
        return $this->foundationUrl;
    }

    /**
     * @param string $foundationUrl
     */
    public function setFoundationUrl(string $foundationUrl): void
    {
        $this->foundationUrl = $foundationUrl;
    }

    /**
     * @return string
     */
    public function getFoundationCustomImageUrl(): string
    {
        return $this->foundationCustomImageUrl;
    }

    /**
     * @param string $foundationCustomImageUrl
     */
    public function setFoundationCustomImageUrl(string $foundationCustomImageUrl): void
    {
        $this->foundationCustomImageUrl = $foundationCustomImageUrl;
    }
	public function getFoundationTitle(): string
    {
        return $this->foundationTitle;
    }
	public function setFoundationTitle(string $foundationTitle): void
    {
		$this->foundationTitle = $foundationTitle;
    }
	public function getFoundationAlt(): string
    {
        return $this->foundationAlt;
    }
	public function setFoundationAlt(string $foundationAlt): void
    {
		$this->foundationAlt = $foundationAlt;
    }
	
}
