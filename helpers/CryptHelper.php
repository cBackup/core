<?php
/**
 * This file is part of cBackup, network equipment configuration backup tool
 * Copyright (C) 2017, Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
 *
 * cBackup is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace app\helpers;


/**
 * @package app\helpers
 */
class CryptHelper
{

    /**
     * Encrypt string with public SSL key.
     * Method has workaround for OpenSSL limit on longer strings.
     *
     * @param  string $source
     * @param  string $pubKey
     * @return string
     */
    public static function encrypt(string $source, string $pubKey): string
    {

        $result    = '';
        $source    = gzcompress($source);
        $pubKey    = openssl_pkey_get_public($pubKey);
        $keyInfo   = openssl_pkey_get_details($pubKey);
        $chunkSize = ceil($keyInfo['bits'] / 8) - 11;

        while($source) {

            $chunk     = substr($source, 0, $chunkSize);
            $source    = substr($source, $chunkSize);
            $encrypted = '';

            if (!openssl_public_encrypt($chunk, $encrypted, $pubKey)) {
                die('Failed to encrypt data');
            }

            $result.= $encrypted;

        }

        openssl_free_key($pubKey);
        return $result;

    }


    /**
     * Decrypt string with private SSL key.
     * Method has workaround for OpenSSL limit on longer strings.
     *
     * @param  string $source
     * @param  string $privKey
     * @return string
     */
    public static function decrypt(string $source, string $privKey): string
    {

        if (!$privKey = openssl_pkey_get_private($privKey)) {
            die('Private Key failed');
        }

        $keyInfo   = openssl_pkey_get_details($privKey);
        $chunkSize = ceil($keyInfo['bits'] / 8);
        $result    = '';

        while($source) {

            $chunk     = substr($source, 0, $chunkSize);
            $source    = substr($source, $chunkSize);
            $decrypted = '';

            if (!openssl_private_decrypt($chunk, $decrypted, $privKey)) {
                die('Failed to decrypt data');
            }

            $result.= $decrypted;

        }

        openssl_free_key($privKey);
        return gzuncompress($result);

    }

}
