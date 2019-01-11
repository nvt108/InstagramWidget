<?php
class Instagram_Widget extends WP_Widget {

    public function __construct() {
        $widget_options = array(
            'classname' => 'instagram_widget',
            'description' => 'This is an Instagram Widget',
        );
        parent::__construct( 'instagram_widget', 'Instagram Widget', $widget_options );
    }
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        $accessToken = apply_filters( 'widget_title', $instance['access_token'] );
        echo $args['before_widget'];
        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];
        ob_start();
        if(!empty($accessToken)){
            $data = file_get_contents('https://api.instagram.com/v1/users/self/?access_token='.$accessToken);
            $data = json_decode($data);
            if(!empty($data)){
                $data = $data->data;
                if(!empty($data->id)){
                    if(empty($userId)) $userId = $data->id;
                    $userName = !empty($data->username) ? $data->username : '';
                    $pictureProfile = !empty($data->profile_picture) ? $data->profile_picture : '';
                    $fullName = !empty($data->full_name) ? $data->full_name : '';
                }
            }
        }
        ?>
        <div class="sidebar-content">
            <div class="textwidget">
                <?php if(!empty($fullName)): ?>
                    <div id="sb_instagram" class="sbi sbi_col_1" style="max-width: 640px; width:100%; padding-bottom: 10px;">
                        <div id="instafeed" class="instagramFeed"></div>
                        <div id="sbi_load">
                            <a class="sbi_load_btn" href="javascript:void(0);" style="">
                                <span id="next-feeds" class="sbi_btn_text" style="opacity: 1;"><?php echo __('Load More...','wpb_widget_domain') ?></span>
                            </a>
                            <div class="sbi_follow_btn">
                                <a target="_blank" href="<?php echo 'https://www.instagram.com/'.$userName; ?>">
                                    <i class="fa fa-instagram" aria-hidden="true"></i>
                                    <?php echo __('Follow on Instagram','wpb_widget_domain') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                var imgs = [];
                var currentPage = 1;
                var loadedPage = 1;
                var feed = new Instafeed({
                    get: 'user',
                    userId: '<?php echo $userId; ?>',
                    accessToken: '<?php echo $accessToken; ?>',
                    resolution: 'low_resolution',
                    limit: 5,
                    template: '<a href="{{link}}" target="_blank"><img src="{{image}}" class="instagramImg"/></a>',
                    after: function () {
                        if (!this.hasNext()) {
                            jQuery('a.sbi_load_btn').hide();
                        }
                    },
                    cachedNext: function () {   // read the cached instagram data
                        var nextImages = imgs.slice((currentPage - 1) * feed.options.limit, (currentPage) * feed.options.limit);
                        jQuery("#instafeed").html(nextImages);
                    },
                    success: function (data) {
                        var images = data.data;
                        var result;
                        for (i = 0; i < images.length; i++) {
                            image = images[i];
                            result = this._makeTemplate(this.options.template, {
                                model: image,
                                id: image.id,
                                link: image.link,
                                image: image.images[this.options.resolution].url
                            });
                            imgs.push(result);
                        }
                    }
                });
                jQuery('#next-feeds').click(function () {
                    currentPage++;
                    if (currentPage > loadedPage) {
                        feed.next();
                        loadedPage++;
                    }
                    else
                        feed.options.cachedNext();
                });
                feed.run();
            });
        </script>
        <?php
        $widget = ob_get_contents();

        ob_end_clean();
        echo $widget;
        echo $args['after_widget'];
    }
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'My Instagram', 'wpb_widget_domain' );
        }
        $accessToken = $instance['access_token'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'access_token' ); ?>"><?php _e( 'Access Token:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'access_token' ); ?>" name="<?php echo $this->get_field_name( 'access_token' ); ?>" type="text" value="<?php echo esc_attr( $accessToken ); ?>" />
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['access_token'] = ( ! empty( $new_instance['access_token'] ) ) ? strip_tags( $new_instance['access_token'] ) : '';
        return $instance;
    }
}
function wpb_load_widget() {
    register_widget( 'Instagram_Widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );