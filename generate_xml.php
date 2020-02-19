<?php
require('/var/www/html/wp-load.php');
#add_action( 'woocommerce_loaded', 'get_product_ids' );
#add_action( 'woocommerce_loaded', 'generate_shopitem' );


function get_product_ids(){
   $test_mode = true;
   $products_IDs = new WP_Query( array(
           'post_type' => 'product',
           'posts_per_page' => -1,
       ));
   
   $ids = array();
   foreach ( $products_IDs->get_posts() as $prid){
   	#if ($test_mode && count($ids) == 2) {
   	#	break;
	#}
	$ids[] = $prid->ID;
   }
   
   echo "Number of prepared product IDs: ".count($ids)."\n";
   return $ids;
}

function generate_xml_for_heureka() {
   $product_ids = get_product_ids();

   print_xml_header();

   foreach ($product_ids as $id) {
      generate_shopitem($id);
   }
   print_xml_footer();
}

function get_image($product,$number) {
   $images=$product->get_gallery_image_ids();
   $image_url = wp_get_attachment_image_url( $images[$number], 'full' );
   return $image_url;
}

function get_delivery_date($product) {
   $status=$product->get_stock_status();
   if ($status == "instock") {
      return 0;  # in stock
   }
   else {
      return 8; # delivery in two weeks
   }
} 

function get_manufacturer($product) {
   # for this attribute, please add new attribute in the product properties (vlastnosti) with the name Manufacturer and value equal to needed string

   $attrib_array = $product->get_attributes();

   # manual added variable "Manufacturer" is represented by "manufacturer", name is directly written in array 
   $manufacturer = $attrib_array["manufacturer"]["options"][0];
   if ($manufacturer == NULL) {
      # variable set to WP "Manufacturer" is represented by "pa_manufacturer" and values are integers to another field...
      $manufacturer = array_shift( wc_get_product_terms( $product->id, 'pa_manufacturer', array( 'fields' => 'names' ) ) );
      if ($manufacturer == NULL) {
         fwrite (STDERR, "Warning: Manufacturer not set for product with id ".$product->get_id()."\n");
         $manufacturer = "";
      }
   }
   return $manufacturer;  
}

function generate_shopitem($product_id) {
   // functions for products are defined in ...wp-content/plugins/woocommerce/includes/abstracts/abstract-wc-product.php
   // Get $product object from product ID
   $product = wc_get_product( $product_id );


   echo '   <SHOPITEM>'.PHP_EOL;
   echo '      <ITEM_ID>'.$product_id.'</ITEM_ID>'.PHP_EOL;
   echo '      <PRODUCTNAME>'.$product->get_name().'</PRODUCTNAME>'.PHP_EOL;
   echo '      <PRODUCT>'.$product->get_name().'</PRODUCT>'.PHP_EOL;
   echo '      <DESCRIPTION>'.product_get_description($product).'</DESCRIPTION>'.PHP_EOL;
   echo '      <URL>'.get_permalink($product_id).'</URL>'.PHP_EOL;
   echo '      <IMGURL>'.get_image($product,0).'</IMGURL>'.PHP_EOL;
   echo '      <IMGURL_ALTERNATIVE>'.get_image($product,1).'</IMGURL_ALTERNATIVE>'.PHP_EOL;
   echo '      <PRICE_VAT>'.$product->get_price().'</PRICE_VAT>'.PHP_EOL;
   echo '      <MANUFACTURER>'.get_manufacturer($product).'</MANUFACTURER>'.PHP_EOL;
   # HARDCODED #
   echo '      <CATEGORYTEXT>Elektronika | Smart domácnosť </CATEGORYTEXT>'.PHP_EOL;
   # /HARDCODED #
   echo '      <DELIVERY_DATE>'.get_delivery_date($product).'</DELIVERY_DATE>'.PHP_EOL;
   # HARDCODED #
   echo '      <DELIVERY>'.PHP_EOL;
   echo '        <DELIVERY_ID>ZASILKOVNA</DELIVERY_ID>'.PHP_EOL;
   echo '        <DELIVERY_PRICE>3,99</DELIVERY_PRICE>'.PHP_EOL;
   echo '      </DELIVERY>'.PHP_EOL;
   # /HARDCODED #
   echo '   </SHOPITEM>'.PHP_EOL;
   
   #Poznamky
   # echo '       <DELIVERY_PRICE_COD></DELIVERY_PRICE_COD>'.PHP_EOL;  # cena s dobierkou. Ak nepodporujeme dobierku, tag neuvadzat
   #
   # Tag MANUFACTURER sa cita z vlastnosti produktu, ktoru si musim rucne zadefinovat ku kazdemu produktu. Napr Manufacturer: Amazon
   #
   # Tagy PARAM a ITEMGROUP_ID sa pouzivaju ak ide o skupinu produktov s variaciou, napriklad tricko v bielej a modrej farbe a v mnohych velkostiach.
   #  PARAM definuju presny popis jedneho tricka, ITEMGROUP_ID je rovnake pre vsetky tricka zo skupiny - U nas zatial nic taketo nemame. 
   # Aj rozne farby echo zariadeni mame vzdy ako zvlast produkt
   #
   # Prislusenstvo k tomuto produktu sa uvadza do tagu <ACCESSORY> item_id</ACCESORY>, kde item_id reprezentuje to prislusenstvo.Je mozne uviest viackrat
   # My zatial nevedieme tuto moznost 
  


   #var_dump($product);


}

function product_get_description($product){
   $dot = ".";
   $content = $product->get_description();

   $position = stripos ($content, $dot); //find first dot position

   if($position) { //if there's a dot in our soruce text do
      $offset = $position + 1; //prepare offset
      $position2 = stripos ($content, $dot, $offset); //find second dot using offset
      $first_two = substr ($content, 0, $position2); //put two first sentences under $first_two

      $first_two=strip_tags($first_two);
      return $first_two.'.'; 
    }
   return "";
}


function print_xml_header(){
   echo "\n\n";
   echo '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL;
   echo '<SHOP>'.PHP_EOL;
}

function print_xml_footer(){
   echo '</SHOP>'.PHP_EOL;
}


// MAIN CODE 
generate_xml_for_heureka();


?>
