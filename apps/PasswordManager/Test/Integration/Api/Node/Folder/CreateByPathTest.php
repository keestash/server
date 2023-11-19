<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Integration\Api\Node\Folder;

use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use Ramsey\Uuid\Uuid;

class CreateByPathTest extends TestCase {

    /**
     * @param string $path
     * @return void
     * @throws \JsonException
     * @throws \KST\Service\Exception\KSTException
     * @dataProvider providePaths
     */
    public function testCreateByPath(string $path): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );
        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_FOLDER_CREATE_BY_PATH
                    , [
                        'path'           => $path
                        , 'delimiter'    => '/'
                        , 'parentNodeId' => 'root'
                        , 'forceCreate'  => false
                    ]
                    , $user
                    , $headers
                )
            );

        $data = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::OK, $response);
        $this->assertArrayHasKey('edge', $data);
        $this->assertIsArray($data['edge']);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    public static function providePaths(): array {
        return [
            ["d7d6fce99050ec1ef3cdc8793a69c873/2060d73c798241f5d0fcf69613ad733f"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/2060d73c798241f5d0fcf69613ad733f/d7d6fce99050ec1ef3cdc8793a69c873"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/2060d73c798241f5d0fcf69613ad733f/dcf5cff83acffd70d4db27e9e465549e"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/2060d73c798241f5d0fcf69613ad733f/840944869198bf41c256cd780e105af1"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/2060d73c798241f5d0fcf69613ad733f/2d74b5e4a902541810858f8fd709cf67"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/a68eaffb4397697e117d439943aebf2a"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/d4ace3cf785bdfd4c0425cd16f0ae3af/3e8685285a3f84c40334974d89e6ad27"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/d4ace3cf785bdfd4c0425cd16f0ae3af/228cc4c791da47d8f6576e33baad51c3"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/d4ace3cf785bdfd4c0425cd16f0ae3af/d743daaebad203679f8ff0f6bc0acd40"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/d4ace3cf785bdfd4c0425cd16f0ae3af/388db721638c3950d1e5fd43a70a4603"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/2c9e8d4e345e501b0f04c5d658ef3af3"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/2c9e8d4e345e501b0f04c5d658ef3af3/8a3c9417a805fbdc3ffbe9ddfc47f5bf"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/bc4177317457f5256c99d52896cb8426"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/f93957d7bf4c7d9e4743ab0e4e116f64"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/197c22ad10d09f77a853251026c8af4d"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/8d60fd99a95ad4f50c0c5cf33f3fe95f"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/31f14aae3e39eee2390132b94f4889e9"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/31f14aae3e39eee2390132b94f4889e9/95f812fd0db9dee8086d7ec2df95f69a"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/625aef60d0877372e4f81da00be94fcd"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/d8e9bea08c8de51027da99f82f68abce"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/e0eaf8f8b469fad9465a430fe464c9d4"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/4fe94bae7ee9f9b88b3a646e5f16b402"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/d70c82b72e28cb9717f4813a247ce8bc"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/a5d2226bee657fdbd22e01093c798722"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/5457e838ebb640c6e66e532c92cea7e0"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/197c22ad10d09f77a853251026c8af4d/6bda6ca347259bb8af2f1b11751ddc63"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/3398c807f37b0f65a1bac17f633352ee"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/3398c807f37b0f65a1bac17f633352ee/dcf5cff83acffd70d4db27e9e465549e"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/3398c807f37b0f65a1bac17f633352ee/7eb64cd70541d067c193b8b84dbff4fb"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/3398c807f37b0f65a1bac17f633352ee/ff0702aeddf40c344b13483cba9078cb"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/3398c807f37b0f65a1bac17f633352ee/0b789502b19bcac6945bcf4592e273db"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/3398c807f37b0f65a1bac17f633352ee/63a7e39d21b6515a1ae5dea8665eb07d"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/145e61e9be7f2f7ea3220b546f099047/3a6f4bca0e86588d1196fc722d8c4135/eb32de0239c8837ff4770279bdfcd19e/280d7415615751600710e5e87835a64b"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/145e61e9be7f2f7ea3220b546f099047/3a6f4bca0e86588d1196fc722d8c4135/eb32de0239c8837ff4770279bdfcd19e/bf737d9307901fcb9e1c3c052b5989f2"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/145e61e9be7f2f7ea3220b546f099047/3a6f4bca0e86588d1196fc722d8c4135/d99760e6d4c2b2347d438ebffb8aca7f"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/145e61e9be7f2f7ea3220b546f099047/3a6f4bca0e86588d1196fc722d8c4135/b27a877466c8263e7ee43c521881307e"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/145e61e9be7f2f7ea3220b546f099047/3a6f4bca0e86588d1196fc722d8c4135/b27a877466c8263e7ee43c521881307e"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/145e61e9be7f2f7ea3220b546f099047/3a6f4bca0e86588d1196fc722d8c4135/4e73c02bee12e3175a0decfe92555ed5"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/145e61e9be7f2f7ea3220b546f099047/3a6f4bca0e86588d1196fc722d8c4135/53caa84fd0fb0422a4bf56c5dbab5f47"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/145e61e9be7f2f7ea3220b546f099047/3a6f4bca0e86588d1196fc722d8c4135/237a2117be07fbc03f0943e36ba365b4"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/145e61e9be7f2f7ea3220b546f099047/3a6f4bca0e86588d1196fc722d8c4135/2ca2faf74ae9cada59f5d925e0c2b147"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/2f330686854d4d7a66383629a22888b8/dd77595a49bc7b5b1ad00b0946f9466d/619ee360919b26dac6abc07a2eaa6a9c"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/01a3efd29336f8c6271f720ccc8608ab"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/01a3efd29336f8c6271f720ccc8608ab/dd77595a49bc7b5b1ad00b0946f9466d"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/01a3efd29336f8c6271f720ccc8608ab/dd77595a49bc7b5b1ad00b0946f9466d/619ee360919b26dac6abc07a2eaa6a9c"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/9e2811ab48538e7d72a5af162bb2660a"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/5ea62975090c02248dcd31c2526675ce"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/dd4cb0bf19af3ce48fe849db73c6eaa1"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/2227f76da0e1547f93f9a2fa3ee020f9"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/92698876ed1e9ffb4d735138d2ea0b2b"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/25e8a9bd797dbc98a916194c922c4108"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/15afebcd03e8a572e31ed6c2a2fa9d67"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/cc15c18d60a0c1fc694b110b678e68e9"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/ba29e98e5fa9a7b272ea87f56eae6f79"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/2cf09c52af8ef44cf19dfac290f43619"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/2cf09c52af8ef44cf19dfac290f43619/0d2529cb330e3b4e057fcec6d000e673"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/83317700ed7c92a5ef47ebb28855e341"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/3a6f4bca0e86588d1196fc722d8c4135/43164724bbb4b65ff2418eabd583eba3"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/b25dfc3c0977adb778d7ee2f25d73838/5f43e1eed98ac9de690254263e01aba7/8a3c9417a805fbdc3ffbe9ddfc47f5bf"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/b25dfc3c0977adb778d7ee2f25d73838/5f43e1eed98ac9de690254263e01aba7/8a3c9417a805fbdc3ffbe9ddfc47f5bf/cb8f787c42162aa1c5c9b1ba5ed0c560"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/b25dfc3c0977adb778d7ee2f25d73838/2b38839da7ced1a0576497849fe0b7cd"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/8a3c9417a805fbdc3ffbe9ddfc47f5bf/6fb40ac64e0f61c9c51d368ded59754a"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/145e61e9be7f2f7ea3220b546f099047/6251025561bd49f2e31940e835e72f32"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/145e61e9be7f2f7ea3220b546f099047/9e0f6372d10e673991dcbdd7adb7b10d"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/145e61e9be7f2f7ea3220b546f099047/278d736f5b6d96685178bdd8ecd92e13"]
            , ["d7d6fce99050ec1ef3cdc8793a69c873/145e61e9be7f2f7ea3220b546f099047/83031d0af9dc9d8e038691c269f5c680"]
        ];
    }

}