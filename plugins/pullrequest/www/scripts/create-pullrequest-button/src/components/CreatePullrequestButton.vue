<!--
  - Copyright (c) Enalean, 2018. All Rights Reserved.
  -
  - This file is a part of Tuleap.
  -
  - Tuleap is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 2 of the License, or
  - (at your option) any later version.
  -
  - Tuleap is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
    <button class="tlp-button-primary"
            v-bind:disabled="is_button_disabled"
            v-bind:title="button_title"
    >
        <i class="fa fa-code-fork fa-rotate-270 tlp-button-icon"></i>
        <translate>Create pull request</translate>
    </button>
</template>

<script>
import store from "../store/index.js";

export default {
    name: "CreatePullrequestButton",
    store,
    computed: {
        is_button_disabled() {
            return !this.$store.getters.can_create_pullrequest;
        },
        button_title() {
            return this.is_button_disabled
                ? this.$gettext("No pull request can currently be created")
                : "";
        }
    }
};
</script>
