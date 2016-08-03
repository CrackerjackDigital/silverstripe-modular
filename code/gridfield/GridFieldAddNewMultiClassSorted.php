<?php
namespace Modular;

use \GridFieldAddNewMultiClass;

/**
 * A component which lets the user select from a list of classes to create a new record form.
 *
 * By default the list of classes that are createable is the grid field's model class, and any
 * subclasses. This can be customised using {@link setClasses()}.
 */
class GridFieldAddNewMultiClassSorted extends GridFieldAddNewMultiClass {
    /**
     * Overrides the parent method so that we can sort the classes by their value
     *
     * @param GridField $grid
     * @return array a map of class name to title
     */
    public function getClasses(GridField $grid) {
        $result = parent::getClasses($grid);
        if (isset($result['BlockModel'])) {
            // never create straight BlockModel
            unset($result['BlockModel']);
        }
        if ($form = $grid->getForm()) {
            if ($record = $form->getRecord()) {
                if ($record->ClassName == 'ChecklistBlock') {
                    unset($result['ChecklistBlock']);
                }
            }
        }
        asort($result);
        return $result;
    }
}