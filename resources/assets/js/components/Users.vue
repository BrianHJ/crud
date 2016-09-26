<template>
    <div>
        <form action="#" @submit.prevent="searchData" method="GET">
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="row">
                        <div class="col-md-2 col-xs-6">
                            <div class="form-group">
                                <label for="name" class="control-label">Name</label>
                                <input type="text" name="name" class="form-control input-sm" v-model="search.name">
                            </div>
                        </div>
                        <div class="col-md-2 col-xs-6">
                            <div class="form-group">
                                <label for="email" class="control-label">Email</label>
                                <input type="text" name="email" class="form-control input-sm" v-model="search.email">
                            </div>
                        </div>
                        <div class="col-md-2 col-xs-6">
                            <div class="form-group">
                                <label for="created_at" class="control-label">Created On</label>
                                <date-picker
                                    :time.sync="search.created_at"
                                    :option="option"
                                >
                                </date-picker>
                            </div>
                        </div>
                        <div class="col-md-2 col-xs-6 pull-right" style="padding-top: 15px;">
                            <div class="row">
                                <div class="col-md-12 col-xs-12 pull-right text-center">
                                    <multiselect
                                        :options = "options"
                                        :selected = "selected"
                                        placeholder = "Showing Per Page"
                                        @update = "updatePerPage"

                                    >
                                    </multiselect>
                                    <span style="margin-top: 5px; font-size: 15px;" v-if="pagination.total">
                                        {{pagination.from}} - {{pagination.to}} ({{pagination.total}})
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="pull-left" style="margin-bottom: 20px;">
                        <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i> Search</button>
                        <button class="btn btn-success btn-sm" @click="createEntry"><i class="fa fa-plus"></i> Create</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-condensed table-hover">
                <tr style="background-color: #a3a3c2;" class="inverse head">
                    <th class="col-md-1 text-center">
                        #
                    </th>
                    <th class="col-md-4 text-center">
                        <a href="#" @click="sortBy('name')">Name</a>
                        <span v-if="sortkey == 'name' && !reverse" class="fa fa-caret-down"></span>
                        <span v-if="sortkey == 'name' && reverse" class="fa fa-caret-up"></span>
                    </th>
                    <th class="col-md-3 text-center">
                        <a href="#" @click="sortBy('email')">Email</a>
                        <span v-if="sortkey == 'email' && !reverse" class="fa fa-caret-down"></span>
                        <span v-if="sortkey == 'email' && reverse" class="fa fa-caret-up"></span>
                    </th>
                    <th class="col-md-3 text-center">
                        <a href="#" @click="sortBy('created_at')">Created On</a>
                        <span v-if="sortkey == 'created_at' && !reverse" class="fa fa-caret-down"></span>
                        <span v-if="sortkey == 'created_at' && reverse" class="fa fa-caret-up"></span>
                    </th>
                    <th class="col-md-1 text-center">
                        Action
                    </th>
                </tr>

                <tr v-for="user in list">
                    <td class="col-md-1 text-center">
                        {{ $index + pagination.from }}
                    </td>
                    <td class="col-md-4 text-left">
                        {{ user.name }}
                    </td>
                    <td class="col-md-3 text-left">
                        {{ user.email }}
                    </td>
                    <td class="col-md-3 text-center">
                        {{ user.created_at }}
                    </td>
                    <td class="col-md-1 text-center">
                        <a href="#" @click="editEntry(user)" class="btn btn-primary btn-sm"><i class="fa fa-pencil-square-o"></i></a>
                        <a href="#" @click="deleteEntry(user)" class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i></a>
                    </td>
                </tr>
                <tr v-if="! pagination.total">
                    <td colspan="14" class="text-center"> No Results Found </td>
                </tr>
            </table>
            <div class="pull-left">
                <pagination :pagination="pagination" :callback="fetchTable" :offset="4"></pagination>
            </div>
        </div>
    </div>

    <div id="modal_create">
        <modal
            :title = "create_title"
            :show.sync = "create_show"
            @ok = "storeEntry"
            :ok-text = "create_submit"
            :ok-class = "create_btn"
            :cancel-class = "cancel_btn"
            :large = "allowSize"
        >
            <form method="POST" action="#">
                <div class="form-group" :class="{'has-error': errors.name.length > 0}">
                    <label for="name" class="control-label">Name</label>
                    <input type="text" name="name" class="form-control" v-model="form.name"/>
                    <span v-if="errors.name" class="error">
                        <span style="color:red;" v-for="nameerror in errors.name">* {{ nameerror }}</span>
                    </span>
                </div>

                <div class="form-group" :class="{'has-error': errors.email.length > 0}">
                    <label for="email" class="control-label">Email</label>
                    <input type="email" name="email" class="form-control" v-model="form.email"/>
                    <span v-if="errors.email" class="error">
                        <span style="color:red;" v-for="emailerror in errors.email">* {{ emailerror }}</span>
                    </span>
                </div>
            </form>
        </modal>
    </div>

    <div id="modal_edit">
        <modal
            :title = "edit_title"
            :show.sync = "edit_show"
            @ok = "updateEntry(form.id)"
            :ok-text = "edit_submit"
            :ok-class = "edit_btn"
            :cancel-class = "cancel_btn"
            :large = "allowSize"
        >
        <!-- :showfooter = "show_editfooter" -->
            <form method="POST" action="#">
                <div class="form-group" :class="{'has-error': errors.name.length > 0}">
                    <label for="name" class="control-label">Name</label>
                    <input type="text" name="name" class="form-control" v-model="form.name"/>
                    <span v-if="errors.name" class="error">
                        <span style="color:red;" v-for="nameerror in errors.name">* {{ nameerror }}</span>
                    </span>
                </div>

                <div class="form-group" :class="{'has-error': errors.email.length > 0}">
                    <label for="email" class="control-label">Email</label>
                    <input type="email" name="email" class="form-control" v-model="form.email"/>
                    <span v-if="errors.email" class="error">
                        <span style="color:red;" v-for="emailerror in errors.email">* {{ emailerror }}</span>
                    </span>
                </div>
            </form>
        </modal>
    </div>
</template>
<!--             <div class="form-group{{ $errors->has('alt_contact') ? ' has-error' : '' }}">
                {!! Form::label('alt_contact', 'Alt Contact', ['class'=>'control-label']) !!}
                {!! Form::text('alt_contact', null, ['class'=>'form-control', 'placeholder'=>'Leave Blank If Not Applicable']) !!}
                {{-- form validation --}}
                @if($errors->has('alt_contact'))
                    <span class="help-block" style="color:red;">{{ $errors->first('alt_contact') }}</span>
                @endif
            </div> -->

<script>
    // Local components
    import Pagination from './Pagination.vue';
    import Modal from './Modal.vue';

    // subject to import (select dropdown)
    import Multiselect from 'vue-multiselect';

    // subject to import (datepicker)
    import DatePicker from 'vue-datepicker';

    export default {
        components: {
            // subject to declare
            Multiselect,
            DatePicker,
            Pagination,
            Modal,
        },
        data() {
            return {
                list: [],
                pagination: {
                    total: 0,
                    from: 1,
                    // required
                    per_page: 1,
                    current_page: 1,
                    last_page: 0,
                    to: 5
                },
                search: {
                    name: '',
                    email: '',
                    created_at: '',
                },
                sortkey: '',
                reverse: false,
                // bootstrap modal
                create_submit: 'Create',
                create_btn: 'btn btn-success',
                create_title: 'New User',
                create_show: false,
                edit_submit: 'Save Changes',
                edit_btn: 'btn btn-success',
                edit_title: '',
                // show_editfooter: false,
                edit_show: false,
                cancel_btn: 'btn btn-default ',
                allowSize: true,
                form: {
                    id: '',
                    name: '',
                    email: '',
                },

                // subject to include (multiselect)
                // subject to change (pagination options)
                options: [
                    {label: '50', value: 50},
                    {label: '100', value: 100},
                    {label: '200 ', value: 200},
                ],

                // subject to include (multiselect)
                // subject to change (pagination default)
                selected: 50,

                // subject to include (datetimepicker)
                option: {
                    type: 'day',
                    week: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
                    month: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    format: 'YYYY-MM-DD',
                    placeholder: '',
                    inputStyle: {
                        'display': 'block',
                        'padding': '6px 12px;',
                        'line-height': '1.5',
                        'font-size': '14px',
                        'border': '1px solid #ccd0d2',
                        'box-shadow': 'inset 0 1px 1px rgba(0, 0, 0, 0.075)',
                        'border-radius': '4px',
                        'color': '#5F5F5F',
                        'width': '100%',
                        'height': '30px',
                        'padding-left': '10px',
                    },
                    color: {
                        header: '#3097D1',
                        headerText: '#ffffff',
                    },
                    buttons: {
                    },
                    overlayOpacity: 0.5,
                    dismissible: true
                },
                errors: {
                    name: [],
                    email: [],
                },
            }
        },
        ready() {
            this.fetchTable();
        },
        methods: {
            fetchTable() {
                let data = {
                    // subject to change (search list and pagination)
                    paginate: this.pagination.per_page,
                    page: this.pagination.current_page,
                    name: this.search.name,
                    email: this.search.email,
                    created_at: this.search.created_at,
                    sortkey: this.sortkey,
                    reverse: this.reverse,
                    per_page: this.selected,
                };
                this.$http.get(
                        // subject to change (search list and pagination)
                        'api/users?page=' + data.page +
                        '&perpage=' + data.per_page +
                        '&sortkey=' + data.sortkey +
                        '&reverse=' + data.reverse +
                        '&name=' + data.name +
                        '&email=' + data.email +
                        '&created_at=' + data.created_at
                    ).then((response) => {
                        this.list = response.data.data.data;
                        this.pagination = response.data.pagination;
                });
            },
            searchData() {
                this.pagination.current_page = 1;
                this.fetchTable();
            },
            sortBy(sortkey) {
                this.pagination.current_page = 1;
                this.reverse = (this.sortkey == sortkey) ? ! this.reverse : false;
                this.sortkey = sortkey;
                this.fetchTable();
            },
            createEntry() {
                this.form = {
                    id: '',
                    name: '',
                    email: '',
                }
                this.errors = {
                    name: [],
                    email: [],
                }
                this.create_show = true;
            },
            storeEntry() {
                this.$http.post('user/', this.form).then((response) => {
                    this.fetchTable();
                    this.create_show = false;
                }, (response) => {
                    var error = JSON.parse(response.body);
                    this.errors = {
                        name: error.name,
                        email: error.email,
                    }
                });
            },
            deleteEntry(data) {
                // subject to change (row delete api)
                this.$http.delete('user/' + data.id).then((response) => {
                    this.fetchTable();
                });
            },
            updatePerPage(data) {
                this.selected = data.value;
                this.fetchTable();
            },
            editEntry(data) {
                this.errors = {
                    name: [],
                    email: [],
                }
                let index = this.list.indexOf(data) + this.pagination.from;
                this.$http.get('user/' + data.id + '/edit').then((response) => {
                    this.form.id = response.data.id;
                    this.form.name = response.data.name;
                    this.form.email = response.data.email;
                    this.edit_title = 'Editing ID: ' + index;
                    this.edit_show = true;
                });
            },
            updateEntry(id) {
                this.$http.patch('user/' + id, this.form).then((response) => {
                    this.edit_show = false;
                    this.edit_title = '';
                    this.form = {
                        id: '',
                        name: '',
                        email: '',
                    }
                    this.fetchTable();
                });
            },
        },
    }
</script>