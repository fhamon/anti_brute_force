<?php

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	/*
	License: MIT
	*/

	class ViewFactory {

		/**
		 *
		 * Utility method that build the header of the table element
		 */
		public static function buildTableHeader(Array $cols) {
			$a = array();
			foreach ($cols as $key => $value) {
				$label = Widget::Label($value);
				$a[] = array($label, 'col');
			}
			return $a;
		}

		/**
		 *
		 * Utility method that build the body of the table element
		 *
		 * ** I wish templating Admin Page was simplier !!!
		 */
		public static function buildTableBody(Array $cols, Array $data) {
			$a = array();

			// update flag
			$_hasData = $data != null && count($data) > 0;

			if (!$_hasData) {
				// no data
				// add a table row with only one cell
				$a = array(
					Widget::TableRow(
						array(
							Widget::TableData(
								__('None found.'), // text
								'inactive', // class
								NULL, // id
								count($cols)  // span
							)
						),'odd'
					)
				);
			} else {

				$index = 0;

				foreach ($data as $datarow) {

					$tds = array();

					$datarow = get_object_vars($datarow);

					foreach ($cols as $key => $value) {
						$val = $datarow[$key];
						$css = 'col';
						$hasValue = strlen($val) > 0;

						if (!$hasValue) {
							$val = __('None');
							$css = 'inactive';
						}

						$td = Widget::TableData($val, $css);

						// add the hidden checkbox for selectacle row
						if ($key == 'IP' && $hasValue) {
							$chk = Widget::Input(
								"ip[$val]", //name
								NULL,
								'checkbox'
							);
							$td->appendChild($chk);
						}

						array_push($tds, $td);
					}

					array_push($a, Widget::TableRow($tds, $index % 2 == 0 ? 'even' : 'odd'));

					$index++;
				}
			}

			return $a;
		}

		/**
		 * Utility method that generates the 'action' panel
		 */
		public static function buildActions($hasData, Array $additionalActions = null) {
			$tableActions = new XMLElement('div');
			$tableActions->setAttribute('class', 'actions');

			if ($hasData == true) {

				$options = array(
					array(NULL, false, __('With Selected...')),
					array('delete', false, __('Delete'), 'confirm'),
				);

				if ($additionalActions != null) {
					array_push($options, $additionalActions);
				}

				$tableActions->appendChild(Widget::Apply($options));
			}

			return $tableActions;
		}


		/**
		 * Utility method that generates the 'sub' menu
		 */
		public static function buildSubMenu(Array $options, $current, $actionkey) {
			$tableActions = new XMLElement('div');
			$tableActions->setAttribute('class', 'actions no-pad');

			$fieldset = new XMLElement('fieldset');

			foreach ($options as $key => $o) {
				$button = new XMLElement('button', __($o));
				$button->setAttribute('name', "action[$actionkey]");
				$button->setAttribute('onclick', "document.location='?list=$key'");

				if ($key == $current) {
					$button->setAttribute('class', 'active');
				}

				$fieldset->appendChild($button);
			}

			$tableActions->appendChild($fieldset);

			return $tableActions;
		}


		/**
		 * Quick utility function to make a input field+label
		 * @param string $settingName
		 * @param string $textKey
		 */
		public static function generateField($settingName, $textKey, $hasErrors, $errors, $type = 'text') {
			$inputText = ABF::instance()->getConfigVal($settingName);
			$inputAttr = array();

			switch ($type) {
				case 'checkbox':
					if ($inputText == 'on') {
						$inputAttr['checked'] = 'checked';
					}
					$inputText = '';
					break;
			}

			// create the label and the input field
			$wrap = new XMLElement('div');
			$wrap->setAttribute('class', 'column');
			$label = Widget::Label();
			$input = Widget::Input(
						'settings[' . ABF::SETTING_GROUP . '][' . $settingName .']',
						(string)$inputText,
						$type,
						$inputAttr
					);

			// set the input into the label
			$label->setValue(__($textKey). ' ' . $input->generate() . $err);

			$wrap->appendChild($label);

			// error management
			if ($hasErrors && isset($errors[$settingName])) {
				// style
				$wrap->setAttribute('class', 'invalid');
				$wrap->setAttribute('id', 'error');
				// error message
				$err = new XMLElement('p', $errors[$settingName]);
				$wrap->appendChild($err);
			}

			return $wrap;
		}
	}