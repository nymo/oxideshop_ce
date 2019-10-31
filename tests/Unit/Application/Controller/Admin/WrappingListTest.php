<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
namespace OxidEsales\EshopCommunity\Tests\Unit\Application\Controller\Admin;

/**
 * Tests for Wrapping_List class
 */
class WrappingListTest extends \OxidTestCase
{

    /**
     * Wrapping_List::Render() test case
     *
     * @return null
     */
    public function testRender()
    {
        // testing..
        $oView = oxNew('Wrapping_List');
        $sTplName = $oView->render();

        // testing view data
        $aViewData = $oView->getViewData();
        $this->assertNull($aViewData["allowSharedEdit"] ?? null);
        $this->assertNull($aViewData["malladmin"] ?? null);
        $this->assertNull($aViewData["updatelist"] ?? null);

        $this->assertEquals('wrapping_list.tpl', $sTplName);
    }
}
