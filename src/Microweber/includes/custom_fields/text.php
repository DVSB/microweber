<?php

    $is_required = (isset($data['options']) == true and isset($data['options']["required"]) == true);
if (!isset( $data['input_class']) and isset($params['input-class'])) {
     $data['input_class'] = $params['input-class'];
} elseif (!isset( $data['input_class']) and  isset($params['input_class'])) {
     $data['input_class'] = $params['input_class'];
} else {
	$data['input_class'] = 'form-control';
	
}
?>

<div class="control-group form-group">
  <label class="mw-ui-label">
    <?php if(isset($data['name']) == true and $data['name'] != ''): ?>
    <?php print ucwords(str_replace('_', ' ', $data['name'])); ?>
    <?php elseif(isset($data['custom_field_name']) == true and $data['custom_field_name'] != ''): ?>
    <?php print $data['custom_field_name'] ?>
    <?php else : ?>
    <?php endif; ?>
  </label>
  <?php if(isset($data['help']) == true and $data['help'] != ''): ?>
  <small  class="mw-custom-field-help"><?php print $data['help'] ?></small>
  <?php endif; ?>
  <?php if(isset($data['options']) == true and isset($data['options']["as_text_area"]) == true): ?>
  <div class="controls">
    <textarea <?php if ($is_required): ?> required="true"  <?php endif; ?> class="mw-ui-field"   data-custom-field-id="<?php print $data["id"]; ?>"  name="<?php print $data["custom_field_name"]; ?>" placeholder="<?php print $data["custom_field_value"]; ?>"></textarea>
  </div>
  <?php else : ?>
  <div class="controls">
    <input type="text"   <?php if ($is_required): ?> required="true"  <?php endif; ?> class="mw-ui-field"   data-custom-field-id="<?php print $data["id"]; ?>"  name="<?php print $data["custom_field_name"]; ?>"  placeholder="<?php print $data["custom_field_value"]; ?>" />
  </div>
  <?php endif; ?>
</div>
