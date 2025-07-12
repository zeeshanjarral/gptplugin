<?php
/**
 * Content Upload Page for FITBOT AI Chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

class Fitbot_Content_Upload_Page {
    
    public function render() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'recipes';
        
        ?>
        <div class="wrap">
            <h1><?php _e('FITBOT Content Management', 'fitbot-ai-chatbot'); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=fitbot-content&tab=recipes" class="nav-tab <?php echo $active_tab === 'recipes' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Recipes', 'fitbot-ai-chatbot'); ?>
                </a>
                <a href="?page=fitbot-content&tab=workouts" class="nav-tab <?php echo $active_tab === 'workouts' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Workouts', 'fitbot-ai-chatbot'); ?>
                </a>
                <a href="?page=fitbot-content&tab=motivation" class="nav-tab <?php echo $active_tab === 'motivation' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Motivation', 'fitbot-ai-chatbot'); ?>
                </a>
                <a href="?page=fitbot-content&tab=pdfs" class="nav-tab <?php echo $active_tab === 'pdfs' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('PDFs', 'fitbot-ai-chatbot'); ?>
                </a>
                <a href="?page=fitbot-content&tab=bulk-upload" class="nav-tab <?php echo $active_tab === 'bulk-upload' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Bulk Upload', 'fitbot-ai-chatbot'); ?>
                </a>
            </nav>
            
            <div class="tab-content">
                <?php
                switch ($active_tab) {
                    case 'recipes':
                        $this->render_recipes_tab();
                        break;
                    case 'workouts':
                        $this->render_workouts_tab();
                        break;
                    case 'motivation':
                        $this->render_motivation_tab();
                        break;
                    case 'pdfs':
                        $this->render_pdfs_tab();
                        break;
                    case 'bulk-upload':
                        $this->render_bulk_upload_tab();
                        break;
                    default:
                        $this->render_recipes_tab();
                }
                ?>
            </div>
        </div>
        
        <style>
        .tab-content {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-top: none;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .fitbot-content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .fitbot-content-card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            background: #f9f9f9;
        }
        
        .fitbot-content-card h4 {
            margin: 0 0 10px 0;
            color: #0073aa;
        }
        
        .fitbot-content-meta {
            font-size: 12px;
            color: #666;
            margin: 5px 0;
        }
        
        .fitbot-upload-area {
            border: 2px dashed #ccc;
            border-radius: 4px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            background: #fafafa;
        }
        
        .fitbot-upload-area.dragover {
            border-color: #0073aa;
            background: #f0f8ff;
        }
        
        .fitbot-quick-actions {
            margin: 20px 0;
        }
        
        .fitbot-quick-actions .button {
            margin-right: 10px;
        }
        
        @media (max-width: 768px) {
            .fitbot-content-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <?php
    }
    
    /**
     * Render recipes tab
     */
    private function render_recipes_tab() {
        $recipes = get_posts(array(
            'post_type' => 'fitbot_recipe',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        ?>
        <div class="fitbot-quick-actions">
            <a href="<?php echo admin_url('post-new.php?post_type=fitbot_recipe'); ?>" class="button button-primary">
                <?php _e('Add New Recipe', 'fitbot-ai-chatbot'); ?>
            </a>
            <a href="<?php echo admin_url('edit.php?post_type=fitbot_recipe'); ?>" class="button">
                <?php _e('Manage All Recipes', 'fitbot-ai-chatbot'); ?>
            </a>
        </div>
        
        <h3><?php _e('Recent Recipes', 'fitbot-ai-chatbot'); ?></h3>
        
        <?php if (!empty($recipes)): ?>
            <div class="fitbot-content-grid">
                <?php foreach ($recipes as $recipe): ?>
                    <?php
                    $prep_time = get_post_meta($recipe->ID, '_fitbot_prep_time', true);
                    $cook_time = get_post_meta($recipe->ID, '_fitbot_cook_time', true);
                    $calories = get_post_meta($recipe->ID, '_fitbot_calories', true);
                    $meal_types = wp_get_post_terms($recipe->ID, 'fitbot_meal_type', array('fields' => 'names'));
                    $plan_levels = wp_get_post_terms($recipe->ID, 'fitbot_plan_level', array('fields' => 'names'));
                    ?>
                    <div class="fitbot-content-card">
                        <h4><?php echo esc_html($recipe->post_title); ?></h4>
                        <div class="fitbot-content-meta">
                            <?php if ($prep_time): ?>
                                <span><?php printf(__('Prep: %d min', 'fitbot-ai-chatbot'), $prep_time); ?></span> |
                            <?php endif; ?>
                            <?php if ($cook_time): ?>
                                <span><?php printf(__('Cook: %d min', 'fitbot-ai-chatbot'), $cook_time); ?></span> |
                            <?php endif; ?>
                            <?php if ($calories): ?>
                                <span><?php printf(__('%d cal', 'fitbot-ai-chatbot'), $calories); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($meal_types)): ?>
                            <div class="fitbot-content-meta">
                                <strong><?php _e('Meal Types:', 'fitbot-ai-chatbot'); ?></strong> <?php echo implode(', ', $meal_types); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($plan_levels)): ?>
                            <div class="fitbot-content-meta">
                                <strong><?php _e('Plans:', 'fitbot-ai-chatbot'); ?></strong> <?php echo implode(', ', $plan_levels); ?>
                            </div>
                        <?php endif; ?>
                        <div style="margin-top: 10px;">
                            <a href="<?php echo get_edit_post_link($recipe->ID); ?>" class="button button-small">
                                <?php _e('Edit', 'fitbot-ai-chatbot'); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php _e('No recipes found. Start by adding your first recipe!', 'fitbot-ai-chatbot'); ?></p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Render workouts tab
     */
    private function render_workouts_tab() {
        $workouts = get_posts(array(
            'post_type' => 'fitbot_workout',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        ?>
        <div class="fitbot-quick-actions">
            <a href="<?php echo admin_url('post-new.php?post_type=fitbot_workout'); ?>" class="button button-primary">
                <?php _e('Add New Workout', 'fitbot-ai-chatbot'); ?>
            </a>
            <a href="<?php echo admin_url('edit.php?post_type=fitbot_workout'); ?>" class="button">
                <?php _e('Manage All Workouts', 'fitbot-ai-chatbot'); ?>
            </a>
        </div>
        
        <h3><?php _e('Recent Workouts', 'fitbot-ai-chatbot'); ?></h3>
        
        <?php if (!empty($workouts)): ?>
            <div class="fitbot-content-grid">
                <?php foreach ($workouts as $workout): ?>
                    <?php
                    $duration = get_post_meta($workout->ID, '_fitbot_duration', true);
                    $difficulty = get_post_meta($workout->ID, '_fitbot_difficulty', true);
                    $workout_types = wp_get_post_terms($workout->ID, 'fitbot_workout_type', array('fields' => 'names'));
                    $plan_levels = wp_get_post_terms($workout->ID, 'fitbot_plan_level', array('fields' => 'names'));
                    ?>
                    <div class="fitbot-content-card">
                        <h4><?php echo esc_html($workout->post_title); ?></h4>
                        <div class="fitbot-content-meta">
                            <?php if ($duration): ?>
                                <span><?php printf(__('%d min', 'fitbot-ai-chatbot'), $duration); ?></span> |
                            <?php endif; ?>
                            <?php if ($difficulty): ?>
                                <span><?php echo esc_html(ucfirst($difficulty)); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($workout_types)): ?>
                            <div class="fitbot-content-meta">
                                <strong><?php _e('Type:', 'fitbot-ai-chatbot'); ?></strong> <?php echo implode(', ', $workout_types); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($plan_levels)): ?>
                            <div class="fitbot-content-meta">
                                <strong><?php _e('Plans:', 'fitbot-ai-chatbot'); ?></strong> <?php echo implode(', ', $plan_levels); ?>
                            </div>
                        <?php endif; ?>
                        <div style="margin-top: 10px;">
                            <a href="<?php echo get_edit_post_link($workout->ID); ?>" class="button button-small">
                                <?php _e('Edit', 'fitbot-ai-chatbot'); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php _e('No workouts found. Start by adding your first workout!', 'fitbot-ai-chatbot'); ?></p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Render motivation tab
     */
    private function render_motivation_tab() {
        $messages = get_posts(array(
            'post_type' => 'fitbot_motivation',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        ?>
        <div class="fitbot-quick-actions">
            <a href="<?php echo admin_url('post-new.php?post_type=fitbot_motivation'); ?>" class="button button-primary">
                <?php _e('Add New Message', 'fitbot-ai-chatbot'); ?>
            </a>
            <a href="<?php echo admin_url('edit.php?post_type=fitbot_motivation'); ?>" class="button">
                <?php _e('Manage All Messages', 'fitbot-ai-chatbot'); ?>
            </a>
        </div>
        
        <h3><?php _e('Recent Motivation Messages', 'fitbot-ai-chatbot'); ?></h3>
        
        <?php if (!empty($messages)): ?>
            <div class="fitbot-content-grid">
                <?php foreach ($messages as $message): ?>
                    <div class="fitbot-content-card">
                        <h4><?php echo esc_html($message->post_title); ?></h4>
                        <div class="fitbot-content-meta">
                            <?php echo wp_trim_words($message->post_content, 20); ?>
                        </div>
                        <div style="margin-top: 10px;">
                            <a href="<?php echo get_edit_post_link($message->ID); ?>" class="button button-small">
                                <?php _e('Edit', 'fitbot-ai-chatbot'); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php _e('No motivation messages found. Start by adding your first message!', 'fitbot-ai-chatbot'); ?></p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Render PDFs tab
     */
    private function render_pdfs_tab() {
        $pdfs = get_posts(array(
            'post_type' => 'fitbot_pdf',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        ?>
        <div class="fitbot-quick-actions">
            <a href="<?php echo admin_url('post-new.php?post_type=fitbot_pdf'); ?>" class="button button-primary">
                <?php _e('Add New PDF', 'fitbot-ai-chatbot'); ?>
            </a>
            <a href="<?php echo admin_url('edit.php?post_type=fitbot_pdf'); ?>" class="button">
                <?php _e('Manage All PDFs', 'fitbot-ai-chatbot'); ?>
            </a>
        </div>
        
        <h3><?php _e('Recent PDF Resources', 'fitbot-ai-chatbot'); ?></h3>
        
        <?php if (!empty($pdfs)): ?>
            <div class="fitbot-content-grid">
                <?php foreach ($pdfs as $pdf): ?>
                    <?php
                    $pdf_url = get_post_meta($pdf->ID, '_fitbot_pdf_url', true);
                    $pdf_size = get_post_meta($pdf->ID, '_fitbot_pdf_size', true);
                    $download_count = get_post_meta($pdf->ID, '_fitbot_download_count', true);
                    $plan_levels = wp_get_post_terms($pdf->ID, 'fitbot_plan_level', array('fields' => 'names'));
                    ?>
                    <div class="fitbot-content-card">
                        <h4><?php echo esc_html($pdf->post_title); ?></h4>
                        <div class="fitbot-content-meta">
                            <?php if ($pdf_size): ?>
                                <span><?php printf(__('Size: %s', 'fitbot-ai-chatbot'), $pdf_size); ?></span> |
                            <?php endif; ?>
                            <span><?php printf(__('Downloads: %d', 'fitbot-ai-chatbot'), intval($download_count)); ?></span>
                        </div>
                        <?php if (!empty($plan_levels)): ?>
                            <div class="fitbot-content-meta">
                                <strong><?php _e('Plans:', 'fitbot-ai-chatbot'); ?></strong> <?php echo implode(', ', $plan_levels); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($pdf_url): ?>
                            <div class="fitbot-content-meta">
                                <a href="<?php echo esc_url($pdf_url); ?>" target="_blank"><?php _e('View PDF', 'fitbot-ai-chatbot'); ?></a>
                            </div>
                        <?php endif; ?>
                        <div style="margin-top: 10px;">
                            <a href="<?php echo get_edit_post_link($pdf->ID); ?>" class="button button-small">
                                <?php _e('Edit', 'fitbot-ai-chatbot'); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php _e('No PDF resources found. Start by adding your first PDF!', 'fitbot-ai-chatbot'); ?></p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Render bulk upload tab
     */
    private function render_bulk_upload_tab() {
        ?>
        <h3><?php _e('Bulk Content Upload', 'fitbot-ai-chatbot'); ?></h3>
        <p><?php _e('Upload multiple content items at once using CSV or JSON format.', 'fitbot-ai-chatbot'); ?></p>
        
        <div class="fitbot-upload-area" id="bulk-upload-area">
            <h4><?php _e('Drop files here or click to upload', 'fitbot-ai-chatbot'); ?></h4>
            <p><?php _e('Supported formats: CSV, JSON', 'fitbot-ai-chatbot'); ?></p>
            <input type="file" id="bulk-upload-input" accept=".csv,.json" style="display: none;" multiple>
            <button type="button" class="button" onclick="document.getElementById('bulk-upload-input').click();">
                <?php _e('Choose Files', 'fitbot-ai-chatbot'); ?>
            </button>
        </div>
        
        <div id="upload-progress" style="display: none;">
            <h4><?php _e('Upload Progress', 'fitbot-ai-chatbot'); ?></h4>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 0%;"></div>
            </div>
            <div class="progress-text">0%</div>
        </div>
        
        <div id="upload-results" style="display: none;">
            <h4><?php _e('Upload Results', 'fitbot-ai-chatbot'); ?></h4>
            <div class="upload-summary"></div>
        </div>
        
        <div class="fitbot-upload-templates">
            <h4><?php _e('Download Templates', 'fitbot-ai-chatbot'); ?></h4>
            <p><?php _e('Use these templates to format your bulk upload files:', 'fitbot-ai-chatbot'); ?></p>
            <div class="template-links">
                <a href="#" class="button" onclick="downloadTemplate('recipes')"><?php _e('Recipes Template (CSV)', 'fitbot-ai-chatbot'); ?></a>
                <a href="#" class="button" onclick="downloadTemplate('workouts')"><?php _e('Workouts Template (CSV)', 'fitbot-ai-chatbot'); ?></a>
                <a href="#" class="button" onclick="downloadTemplate('motivation')"><?php _e('Motivation Template (CSV)', 'fitbot-ai-chatbot'); ?></a>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var uploadArea = $('#bulk-upload-area');
            var fileInput = $('#bulk-upload-input');
            
            uploadArea.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });
            
            uploadArea.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });
            
            uploadArea.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                var files = e.originalEvent.dataTransfer.files;
                handleFiles(files);
            });
            
            fileInput.on('change', function() {
                handleFiles(this.files);
            });
            
            function handleFiles(files) {
                if (files.length === 0) return;
                
                $('#upload-progress').show();
                $('#upload-results').hide();
                
                var formData = new FormData();
                for (var i = 0; i < files.length; i++) {
                    formData.append('files[]', files[i]);
                }
                formData.append('action', 'fitbot_bulk_upload');
                formData.append('nonce', '<?php echo wp_create_nonce('fitbot_bulk_upload'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                var percentComplete = (e.loaded / e.total) * 100;
                                $('.progress-fill').css('width', percentComplete + '%');
                                $('.progress-text').text(Math.round(percentComplete) + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        $('#upload-progress').hide();
                        $('#upload-results').show();
                        
                        if (response.success) {
                            $('.upload-summary').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                        } else {
                            $('.upload-summary').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        $('#upload-progress').hide();
                        $('#upload-results').show();
                        $('.upload-summary').html('<div class="notice notice-error"><p><?php _e('Upload failed. Please try again.', 'fitbot-ai-chatbot'); ?></p></div>');
                    }
                });
            }
        });
        
        function downloadTemplate(type) {
            var templates = {
                'recipes': 'title,content,ingredients,instructions,prep_time,cook_time,servings,calories,carbs,fats,protein,meal_type,plan_level,condition\n"Healthy Breakfast Bowl","A nutritious start to your day","Oats, berries, nuts","Mix ingredients and serve",10,0,1,350,45,12,15,"breakfast","start",""',
                'workouts': 'title,content,duration,difficulty,equipment,exercises,workout_type,plan_level,condition\n"Morning Cardio","Quick cardio session",20,"beginner","none","Jumping jacks, burpees","cardio","start",""',
                'motivation': 'title,content\n"Daily Motivation","You are stronger than you think!"'
            };
            
            var content = templates[type];
            var blob = new Blob([content], { type: 'text/csv' });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = type + '_template.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
        </script>
        
        <style>
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #f1f1f1;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: #0073aa;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            text-align: center;
            font-weight: bold;
        }
        
        .template-links {
            margin: 15px 0;
        }
        
        .template-links .button {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        </style>
        <?php
    }
}
