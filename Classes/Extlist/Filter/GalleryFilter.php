<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Daniel Lienert <lienert@punkt.de>, Michael Knoll <mimi@kaktusteam.de>
*  All rights reserved
*
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Class implements the gallery->album filter
 * 
 * @author Daniel Lienert <lienert@punkt.de>
 * @author Michael Knoll <mimi@kaktusteam.de>
 * @package Extlist
 * @subpackage Filter
 */
class Tx_Yag_Extlist_Filter_GalleryFilter extends Tx_PtExtlist_Domain_Model_Filter_AbstractFilter {	

	/**
	 * YAG ConfigurationBuilder
	 * @var Tx_Yag_Domain_Configuration_ConfigurationBuilder
	 */
	protected $yagConfigurationBuilder;
	
	
	
	/**
	 * array of filter values
	 * 
	 * @var array
	 */
	protected $filterValues;
	
	

	/**
	 * Selected gallery
	 * @var int galleryUid
	 */
	protected $galleryUid;

	
	
	/**
	 * Constructor for gallery filter
	 */
	public function __construct() {
		parent::__construct();
		
		$this->yagConfigurationBuilder = Tx_Yag_Domain_Configuration_ConfigurationBuilderFactory::getInstance();
	}
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see Classes/Domain/Model/Filter/Tx_PtExtlist_Domain_Model_Filter_AbstractOptionsFilter::initFilterByTsConfig()
	 */
	protected function initFilterByTsConfig() {
		$this->galleryUid = $this->yagConfigurationBuilder->buildGalleryConfiguration()->getSelectedGallery();
	}
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see Classes/Domain/Model/Filter/Tx_PtExtlist_Domain_Model_Filter_AbstractSingleValueFilter::initFilterByGpVars()
	 */
	protected function initFilterByGpVars() {
		if(array_key_exists('galleryUid', $this->gpVarFilterData)) {
			$this->galleryUid = $this->gpVarFilterData['galleryUid'];
		}
	}	
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see Classes/Domain/Model/Filter/Tx_PtExtlist_Domain_Model_Filter_AbstractFilter::initGenericFilterBySession()
	 */
	public function initFilterBySession() {
		if(array_key_exists('galleryUid', $this->sessionFilterData)) {
			$this->galleryUid = $this->sessionFilterData['galleryUid'];
		}
	}
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see Classes/Domain/Model/Filter/Tx_PtExtlist_Domain_Model_Filter_AbstractSingleValueFilter::persistToSession()
	 */
	public function persistToSession() {
		return array('galleryUid' => $this->galleryUid);
	}
	
	
	
	/**
	 * @see Tx_PtExtlist_Domain_Model_Filter_FilterInterface::reset()
	 *
	 */
	public function reset() {
		$this->galleryUid = null;
		$this->filterQuery = new Tx_PtExtlist_Domain_QueryObject_Query();
		$this->init();
	}
	
	
	
	public function initFilter() {}	
	public function getFilterValueForBreadCrumb() {}
	public function buildFilterCriteria(Tx_PtExtlist_Domain_Configuration_Data_Fields_FieldConfig $fieldIdentifier) {}
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see Classes/Domain/Model/Filter/Tx_PtExtlist_Domain_Model_Filter_AbstractFilter::setActiveState()
	 */
	public function setActiveState() {
		if($this->galleryUid > 0) {
		    $this->isActive = true;
		}
	}
	
	
	
	/**
	 * Build the filterCriteria for filter 
	 * 
	 * @return Tx_PtExtlist_Domain_QueryObject_Criteria
	 */
	protected function buildFilterCriteriaForAllFields() {
		if($this->galleryUid) {
			// TODO think about better solution than to hard-code identifiers here!
			$albumField = $this->fieldIdentifierCollection->getFieldConfigByIdentifier('albumUid');
			$fieldName = Tx_PtExtlist_Utility_DbUtils::getSelectPartByFieldConfig($albumField);

			// Get alum UIDs for selected gallery - as album:gallery is M:N we have to do a little work here!
			$galleryRepository = t3lib_div::makeInstance('Tx_Yag_Domain_Repository_GalleryRepository'); /* @var $galleryRepository Tx_Yag_Domain_Repository_GalleryRepository */
			$gallery = $galleryRepository->findByUid(intval($this->galleryUid)); /* @var $gallery Tx_Yag_Domain_Model_Gallery */
			$albums = $gallery->getAlbums();
			$albumUids = array();
			foreach ($albums as $album) { /* @var $album Tx_Yag_Domain_Model_Album */
				$albumUids[] = $album->getUid();
			}
			
			// Use IN criteria to find all albums that are connected to gallery
			$criteria = Tx_PtExtlist_Domain_QueryObject_Criteria::in($fieldName, $albumUids);
		}
		
		return $criteria;
	}
	
	
	
	/**
	 * Set the gallery Uid
	 * 
	 * @param int $galleryUid UID of gallery that filter should select
	 */
	public function setGalleryUid($galleryUid) {
		$this->galleryUid = $galleryUid;
		$this->sessionFilterData['galleryUid'] = $this->galleryUid;
		$this->init();
		
	}
}