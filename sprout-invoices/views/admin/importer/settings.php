<div id="importer_admin">
	<div id="col-container" class="wp-clearfix">
		<div id="col-left">
			<div id="sticky-wrapper" class="sticky-wrapper">
				<ul class="si-left-nav">
					<a href='#import_start' class="si-tab-item" v-on:click="makeTabActive('start')" v-bind:class="{ active : isActiveTab('start') == true }"><?php esc_html_e( 'Overview', 'sprout-invoices' ); ?></a>
					<?php foreach ( $importers as $class => $label ) : ?>
						<?php if ( method_exists( $class, 'get_id' ) ) : ?>
							<?php
								$id = call_user_func( array( $class, 'get_id' ) );
							?>
							<a href='#import_<?php echo esc_attr( $id ); ?>' class="si-tab-item" v-on:click="makeTabActive('<?php echo esc_attr( $id ); ?>')" v-bind:class="{ active : isActiveTab('<?php echo esc_attr( $id ); ?>') == true }">
								<?php
									printf(
									// translators: 1: importer label.
									esc_html__( '%1$s Import', 'sprout-invoices' ),
									esc_html( $label )
									);
								?>
							</a>
						<?php endif ?>
					<?php endforeach ?>
					<a href='#tools_settings' class="si-tab-item" v-on:click="makeTabActive('tools')" v-bind:class="{ active : isActiveTab('tools') == true }"></span> <?php esc_html_e( 'Developer Tools', 'sprout-invoices' ); ?></a>
				</ul>
			</div>
		</div>
		<div id="col-right" class="si_settings_tabs">
			<div class="si-box">
				<div id="start" class="row" v-show="isActiveTab('start')">
					<h2><?php esc_html_e( 'Welcome to Sprout Invoice\'s Tools', 'sprout-invoices' ); ?></h2>
					<p><?php esc_html_e( 'Here you are able to use the import tool or set developer settings', 'sprout-invoices' ); ?></p>
				</div>
					<?php foreach ( $importers as $class => $label ) :  ?>
						<?php if ( method_exists( $class, 'get_id' ) ) :  ?>
							<?php
								$id = call_user_func( array( $class, 'get_id' ) );
								$settings = call_user_func( array( $class, 'get_options' ) ); ?>

							<div id="<?php echo esc_attr( $id ) ?>" class="row" v-show="isActiveTab('<?php echo esc_attr( $id ) ?>')">
								<?php foreach ( $settings as $key => $section_settings ) :  ?>
									<div id="section_<?php echo esc_attr( $key ) ?>">
										<div class="si-box-top">
											<?php if ( isset( $section_settings['title'] ) && '' !== $section_settings['title'] ) :  ?>
												<?php echo esc_html( $section_settings['title'] ) ?>
											<?php endif ?>
										</div>
										<div class="si-box-bottom">
											<?php if ( isset( $section_settings['description'] ) && '' !== $section_settings['description'] ) :  ?>
												<?php $wp_kses_section_setting = array(
													'div' => array(
														'class' => array(),
													),
													'img' => array(
														'src'   => array(),
													)
												)
												?>
												<p><?php echo wp_kses( $section_settings['description'], $wp_kses_section_setting ) ?></p>
											<?php endif ?>
											<?php if ( ! empty( $section_settings['settings'] ) ) : ?>
												<form id="form_<?php echo esc_attr( $id ); ?>" action="" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
														<?php do_action( 'si_display_settings', $section_settings['settings'] ); ?>
														<input type="hidden" name="importer" value="<?php echo esc_attr( $class ); ?>" />
														<button type="submit" form="form_<?php echo esc_attr( $id ); ?>" value="Submit" class="button button-primary si-import-button">
															<?php
																printf(
																// translators: 1: importer label
																esc_html__( 'Start %1$s Import', 'sprout-invoices' ),
																esc_html( $label )
																);
															?>
														</button>
												</form>
											<?php endif ?>
										</div>
									</div>
								<?php endforeach ?>
							</div>
						<?php endif ?>
					<?php endforeach ?>
				<div class="tools_settings">
					<div id="tools" class="row" v-show="isActiveTab('tools')">
						<h1><?php esc_html_e( 'Developer Tools', 'sprout-invoices' ); ?></h1>
						<?php foreach ( $tool_settings as $tool_setting ) : ?>
						<div id="section_tool">
							<div class="si-box-top">
								<?php echo esc_html( $tool_setting['title'] ); ?>
							</div>
							<div class="si-box-bottom">
								<?php do_action( 'si_display_settings', $tool_setting['settings'], true ); ?>
								<div>
								<?php if ( isset( $tool_setting['link'] ) && ! empty( $tool_setting['link'] ) ) : ?>
									<a href="<?php echo esc_url( $tool_setting['link'] ); ?>" target="_blank" class="button button-primary">View Logs</a>
								<?php endif ?>
								</div>
							</div>
						</div>
						<?php endforeach ?>
						<div class="si-controls">
								<button
									@click='saveOptions'
									:disabled='isSaving'
									id='si-submit-settings' class="button button-primary"><?php esc_html_e( 'Save Changes', 'sprout-invoices' ); ?></button>
									<img
									v-if='isSaving == true'
									id='loading-indicator' src='<?php get_site_url(); ?>/wp-admin/images/wpspin_light-2x.gif' alt='Loading indicator' />
						</div>
						<p v-if='message'>{{ message }}</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
