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
   	if ($test_mode && count($ids) == 2) {
   		break;
	}
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

function generate_shopitem($product_id) {
   // functions for products are defined in ...wp-content/plugins/woocommerce/includes/abstracts/abstract-wc-product.php
   // Get $product object from product ID
   $product = wc_get_product( $product_id );


   echo '   <SHOPITEM>'.PHP_EOL;
   echo '      <ITEM_ID>'.$product_id.'</ITEM_ID>'.PHP_EOL;
   echo '      <PRODUCTNAME>'.$product->get_name().'</PRODUCTNAME>'.PHP_EOL;
   echo '      <PRODUCT>'.$product->get_name().'</PRODUCT>'.PHP_EOL;
   echo '      <DESCRIPTION>'.product_get_description($product).'</DESCRIPTION>'.PHP_EOL;

   echo '   </SHOPITEM>'.PHP_EOL;

 

   #echo "Product(".$product_id."):".$product->get_name()."\n";
   #echo "Slug:".$product->get_slug()."\n";
   #echo "Price:".$product->get_price()."\n";
   #echo "Categories:\n";
   #foreach ($product->get_category_ids() as $id){
   #        if( $term = get_term_by( 'id', $id, 'product_cat' ) ){
   # 		echo $term->name;
   #     	echo "|"; 
   #        }
   #}
   #echo "\n";
   #echo "Permalink:".get_permalink( $product->get_id() )."\n";
## #  $product->get_image_id();
## #  $product->get_image();
   #$images=$product->get_gallery_image_ids();
   #$image1_url = wp_get_attachment_image_url( $images[0], 'full' );
   #$image2_url = wp_get_attachment_image_url( $images[1], 'full' );
   #echo "Image1:".$image1_url."\n";
   #echo "Image2:".$image2_url."\n";
   #echo "\n\n";
#  # echo "Description:".$product->get_description()."\n";

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
