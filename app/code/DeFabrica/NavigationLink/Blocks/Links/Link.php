<?php
namespace DeFabrica\NavigationLink\Block\Links;

class Link extends \Magento\Framework\View\Element\Html\Link
{
public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    array $data = []
) {
    parent::__construct($context, $data);
}
public function getHref(){
 
    $page_url = 'cms_page_url_key'; 
    return $this->getUrl($page_url);
}
public function getLabel(){
     return 'Link_Name';
}
}