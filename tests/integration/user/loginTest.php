<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 * @version   OXID eShop CE
 */

class Integration_User_loginTest extends OxidTestCase
{
    public function tearDown()
    {
        parent::tearDown();
        $oDbRestore = $this->_getDbRestore();
        $oDbRestore->restoreTable('oxuser');
    }

    /**
     * Tries to login with password which is generated with old algorithm
     * and checks if password and salt were regenerated.
     */
    public function testLoginWithOldPassword()
    {
        $sUserName = '_testUserName@oxid-esales.com';
        $sPassword = '_testPassword';
        // Password encoded with old algorithm
        $sOldEncodedPassword = '4bb11fbb0c6bf332517a7ec397e49f1c';
        $sOldSalt = '3262383936333839303439393466346533653733366533346137326666393632';

        $oUser = $this->_createUser($sUserName, $sOldEncodedPassword, $sOldSalt);
        $this->_login($sUserName, $sPassword);

        $oUser->load($oUser->getId());

        $this->assertSame($oUser->getId(), oxRegistry::getSession()->getVariable('usr'), 'User ID is missing in session.');
        $this->assertNull(oxRegistry::getSession()->getVariable('Errors'), 'User did not logged in successfully.');
        $this->assertNotSame($sOldEncodedPassword, $oUser->oxuser__oxpassword->value, 'Old and new passwords must not match.');
        $this->assertNotSame($sOldSalt, $oUser->oxuser__oxpasssalt->value, 'Old and new salt must not match.');
    }

    /**
     * Tries to login with password which was generated using new algorithm
     * and checks if password and salt were not regenerated.
     */
    public function testLoginWithNewPassword()
    {
        $sUserName = '_testUserName@oxid-esales.com';
        $sPassword = '_testPassword';
        // Password encoded with new algorithm
        $sNewSalt = 'INSERT NEW SALT HERE';
        $sNewEncodedPassword = 'INSERT NEW PASSWORD HERE';

        $oUser = $this->_createUser($sUserName, $sNewEncodedPassword, $sNewSalt);
        $this->_login($sUserName, $sPassword);

        $oUser->load($oUser->getId());

        $this->assertSame($oUser->getId(), oxRegistry::getSession()->getVariable('usr'), 'User ID is missing in session.');
        $this->assertNull(oxRegistry::getSession()->getVariable('Errors'), 'User did not logged in successfully.');
        $this->assertSame($sNewEncodedPassword, $oUser->oxuser__oxpassword->value, 'Password in database must match with new password.');
        $this->assertSame($sNewSalt, $oUser->oxuser__oxpasssalt->value, 'Salt in database must match with new salt.');
    }

    public function providerNotSuccessfulLogin()
    {
        return array(
            // Not successful login with old password
            array('_testUserName@oxid-esales.com', '4bb11fbb0c6bf332517a7ec397e49f1c', '3262383936333839303439393466346533653733366533346137326666393632'),
            // Not successful login with new password
            array('_testUserName@oxid-esales.com', 'INSERT NEW PASSWORD HERE', 'INSERT NEW SALT HERE'),
        );
    }

    /**
     * Tries to login with wrong password and checks if password and salt were not changed.
     *
     * @dataProvider providerNotSuccessfulLogin
     */
    public function testNotSuccessfulLogin($sUserName, $sEncodedPassword, $sSalt)
    {
        $oUser = $this->_createUser($sUserName, $sEncodedPassword, $sSalt);
        $sPasswordWrong = 'wrong_password';
        $this->_login($sUserName, $sPasswordWrong);

        $oUser->load($oUser->getId());

        $this->assertNull(oxRegistry::getSession()->getVariable('usr'), 'User ID should be null in session.');
        $this->assertNotNull(oxRegistry::getSession()->getVariable('Errors'), 'Errors should be set in session.');
        $this->assertSame($sEncodedPassword, $oUser->oxuser__oxpassword->value, 'Password must be same.');
        $this->assertSame($sSalt, $oUser->oxuser__oxpasssalt->value, 'Salt must be same.');
    }

    /**
     * @param string $sUserName
     * @param string $sEncodedPassword
     * @param string $sSalt
     *
     * @return oxUser
     */
    private function _createUser($sUserName, $sEncodedPassword, $sSalt)
    {
        $oUser = new oxUser();
        $oUser->oxuser__oxusername = new oxField($sUserName, oxField::T_RAW);
        $oUser->oxuser__oxpassword = new oxField($sEncodedPassword, oxField::T_RAW);
        $oUser->oxuser__oxpasssalt = new oxField($sSalt, oxField::T_RAW);
        $oUser->save();

        return $oUser;
    }

    /**
     * @param string $sUserName
     * @param string $sPassword
     */
    private function _login($sUserName, $sPassword)
    {
        $this->setRequestParam('lgn_usr', $sUserName);
        $this->setRequestParam('lgn_pwd', $sPassword);
        $oCmpUser = new oxcmp_user();
        $oCmpUser->login();
    }
}
