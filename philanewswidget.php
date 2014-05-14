<?php
/*
Plugin Name: PhilaNews
Description: Display news items from feed.
Version: 1.0
*/

class PhilaNews extends WP_Widget {

  function PhilaNews() {
     /* Widget settings. */
    $widget_ops = array(
      'classname' => 'PhilaNews',
      'description' => 'Display news items from feed.');

     /* Widget control settings. */
    $control_ops = array(
       'width' => 250,
       'height' => 250,
       'id_base' => 'PhilaNews');

    /* Create the widget. */
   $this->WP_Widget('PhilaNews', 'News from feed', $widget_ops, $control_ops );
  }

  function form ($instance) {
    /* Set up some default widget settings. */
    $defaults = array('numberposts' => '5','title'=>'','rss'=>'');
    $instance = wp_parse_args( (array) $instance, $defaults ); ?>

  <p>
    <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
    <input type="text" name="<?php echo $this->get_field_name('title') ?>" id="<?php echo $this->get_field_id('title') ?> " value="<?php echo $instance['title'] ?>" size="20">
  </p>
 
  <p>
   <label for="<?php echo $this->get_field_id('numberposts'); ?>">Number of posts:</label>
   <select id="<?php echo $this->get_field_id('numberposts'); ?>" name="<?php echo $this->get_field_name('numberposts'); ?>">
   <?php for ($i=1;$i<=20;$i++) {
     echo '<option value="'.$i.'"';
     if ($i==$instance['numberposts']) echo ' selected="selected"';
       echo '>'.$i.'</option>';
     } ?>
   </select>
  </p>

  <p>
    <label for="<?php echo $this->get_field_id('rss'); ?>">News Feed Address:</label>
    <input type="text" name="<?php echo $this->get_field_name('rss') ?>" id="<?php echo $this->get_field_id('rss') ?> " value="<?php echo $instance['rss'] ?>" size="50">
  </p>

  <?php
}

function update ($new_instance, $old_instance) {
  $instance = $old_instance;

  $instance['numberposts'] = $new_instance['numberposts'];
  $instance['title'] = $new_instance['title'];
  $instance['rss'] = $new_instance['rss'];

  return $instance;
}

function widget ($args,$instance) {
    extract($args);

    $title = $instance['title'];
    $numberposts = $instance['numberposts'];
    $rss = $instance['rss'];
    
    //Add the item limit to our URL by adding ?$top=XX to the end of the URL
    $url = trim($rss).('?$top=').trim($numberposts);

    //This bit opens an HTTP connection and gets the JSON data
    $mycurl = curl_init();
    curl_setopt($mycurl, CURLOPT_HEADER, 0);
    curl_setopt($mycurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($mycurl, CURLOPT_URL, $url);
    curl_setopt($mycurl, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1');
    curl_setopt($mycurl, CURLOPT_HTTPHEADER,array('Accept: application/json;odata=verbose')); //Important bit that forces SharePoint to return JSON
    curl_setopt($mycurl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($mycurl, CURLOPT_SSL_VERIFYHOST, FALSE);
    $webResponse =  curl_exec($mycurl);
    $resultsJSON = json_decode($webResponse, true);//Decodes the returned string as a JSON Array

    //Add the widget to the page
    echo $before_widget;
    echo $before_title.$title.$after_title;
    echo '<div style="background-color: #FAFAFA; border-radius: 10px; padding: 10px"';
    
    $articlesArray = $resultsJSON['d']['results'];//Convert JSON to normal array
    echo '<h1>Items returned from SharePoint</h1>';
    echo'<ul>';
    foreach ($articlesArray as $value) {
       echo '<li>'.$value['Title'].'</li>'; //We're just grabbing the Title field. Other fields can be access by name.
    }
    echo '</ul>';
    echo '</div>';

    echo $after_widget;
 }
}


//add_action('widgets_init', 'ahspfc_load_widgets');
add_action(
'widgets_init',
create_function('','return register_widget("PhilaNews");')
);

?>
