<?php


 class SearchController extends SearchControllerCore
 {

    protected function getAjaxProductSearchVariables()
    {
        $search = $this->getProductSearchVariables();

        $rendered_products_top = $this->render('catalog/_partials/products-top', array('listing' => $search));
        $rendered_products = $this->render('catalog/_partials/products',
            array('listing' => $search,
            'static_token' => Tools::getToken(false), // added token
            'configuration' => $this->getTemplateVarConfiguration(),
            'urls' => array('pages'=>array('cart'=>$this->context->link->getPageLink('cart'))))); 
        $rendered_products_bottom = $this->render('catalog/_partials/products-bottom', array('listing' => $search));

        $data = array(
            'rendered_products_top' => $rendered_products_top,
            'rendered_products' => $rendered_products,
            'rendered_products_bottom' => $rendered_products_bottom,
        );

        foreach ($search as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

   

 }
