/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

export class ShareService {

    constructor(axios) {
        this.axios = axios;
    }

    /**
     * TODO endpoint should be a part of this class, not a @param
     *
     * @param nodeId
     * @param userId
     * @param endpoint
     * @returns {Promise<*>}
     */
    async shareWith(
        nodeId
        , userId
        , endpoint
    ) {
        const _this = this;

        return _this.axios.post(
            endpoint
            , {
                'node_id': nodeId
                , "user_id_to_share": userId
            }
        )
    }
}
