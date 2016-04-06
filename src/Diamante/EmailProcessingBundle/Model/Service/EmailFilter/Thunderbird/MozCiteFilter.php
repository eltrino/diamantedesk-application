<?php
namespace Diamante\EmailProcessingBundle\Model\Service\EmailFilter\Thunderbird;

use DOMElement;
use EmailCleaner\FilterAbstract;

class MozCiteFilter extends FilterAbstract
{
    public function run()
    {
        $signature = $this->dom->find(".moz-cite-prefix");
        if (!$signature->count()) {
            return;
        }

        /* @var $el DOMElement */
        $signature->each(function(DOMElement $el) {
            $nexts = [];
            $next = $el;
            while ($next = $next->nextSibling) {
                $nexts[] = $next;
            }
            foreach ($nexts as $item) {
                pq($item)->remove();
            }
        });


        $signature->remove();
    }
}