<script>
    import injection from '../helpers/injection';

    export default {
        beforeRouteEnter(to, from, next) {
            injection.loading.start();
            injection.http.post(`${window.api}/content/article`, {
                id: to.params.id,
            }).then(response => {
                const article = response.data.data;
                next(vm => {
                    vm.categories = response.data.categories;
                    vm.form.category = article.category?article.category.path:{};
                    vm.form.content = article.content;
                    vm.form.summery = article.summery;
                    vm.form.created_at = article.created_at;
                    vm.form.description = article.description;
                    vm.form.image = article.thumb_image;
                    vm.form.informations = article.category?article.category.informations:{};
                    vm.form.is_hidden = article.is_hidden === 1;
                    vm.form.is_sticky = article.is_sticky === 1;
                    vm.form.source = {
                        author: article.source_author,
                        link: article.source_link,
                    };
                    vm.form.title = article.title;
                    injection.loading.finish();
                    injection.message.info(injection.trans('content.article.info.get'));
                });
            }).catch(() => {
                injection.loading.fail();
            });
        },
        data() {
            return {
                action: `${window.api}/content/upload`,
                categories: [],
                form: {
                    category: [],
                    content: '',
                    created_at: '',
                    description: '',
                    image: '',
                    informations: [],
                    is_hidden: false,
                    is_sticky: false,
                    keyword: [],
                    summery: '',
                    source: {
                        author: '',
                        link: '',
                    },
                    title: '',
                },
                loading: false,
                path: window.UEDITOR_HOME_URL,
                rules: {
                    content: [
                        {
                            required: true,
                            type: 'string',
                            message: injection.trans('content.article.form.content.error'),
                            trigger: 'change',
                        },
                    ],
                    title: [
                        {
                            required: true,
                            type: 'string',
                            message: injection.trans('content.article.form.title.error'),
                            trigger: 'change',
                        },
                    ],
                },
                trans: injection.trans,
            };
        },
        methods: {
            categoryChanged(path, data) {
                if (data.length) {
                    this.form.informations = data[data.length - 1].informations;
                }
            },
            removeImage() {
                this.form.image = '';
            },
            submit() {
                const self = this;
                self.loading = true;
                self.$refs.form.validate(valid => {
                    if (valid) {
                        const formData = new window.FormData();
                        window.console.log(self.form);
                        formData.append('category_id', self.form.category.length ? self.form.category[self.form.category.length - 1] : 0);
                        formData.append('content', self.form.content);
                        formData.append('summery', self.form.summery);
                        formData.append('created_at', self.form.created_at);
                        formData.append('description', self.form.description);
                        formData.append('is_hidden', self.form.is_hidden ? '1' : '0');
                        formData.append('id', self.$route.params.id);
                        formData.append('is_sticky', self.form.is_sticky ? '1' : '0');
                        formData.append('informations', JSON.stringify(self.form.informations));
                        formData.append('keyword', self.form.keyword);
                        formData.append('thumb_image', self.form.image ? self.form.image : '');
                        formData.append('title', self.form.title);
                        formData.append('source_author', self.form.source.author);
                        formData.append('source_link', self.form.source.link);
                        self.$http.post(`${window.api}/content/article/edit`, formData).then(() => {
                            self.$notice.open({
                                title: '编辑文章信息成功！',
                            });
                            self.$router.push('/content/article');
                        }).catch(() => {
                            self.$notice.error({
                                title: '编辑文章信息失败！',
                            });
                        }).finally(() => {
                            self.loading = false;
                        });
                    } else {
                        self.loading = false;
                        self.$notice.error({
                            title: injection.trans('content.article.info.error'),
                        });
                    }
                });
            },
            uploadBefore() {
                this.$loading.start();
            },
            uploadError(error, data) {
                const self = this;
                self.$loading.error();
                Object.keys(data.message).forEach(index => {
                    self.$notice.error({
                        title: data.message[index],
                    });
                });
            },
            uploadFormatError(file) {
                this.$notice.warning({
                    title: '文件格式不正确',
                    desc: `文件 ${file.name} 格式不正确，请上传 jpg 或 png 格式的图片。`,
                });
            },
            uploadSuccess(data) {
                const self = this;
                self.$loading.finish();
                self.$notice.open({
                    title: data.message,
                });
                self.form.image = data.data.path;
            },
        },
        watch: {
            form: {
                deep: true,
                handler(val) {
                    window.console.log(val);
                },
            },
        },
    };
</script>
<template>
    <div class="article-wrap">
        <div class="article-edit">
            <i-form label-position="top" :model="form" ref="form" :rules="rules">
                <row :gutter="20">
                    <i-col span="16">
                        <card :bordered="false">
                            <form-item prop="title">
                                <i-input :placeholder="trans('content.article.form.title.placeholder')"
                                         v-model="form.title"></i-input>
                            </form-item>
                            <form-item prop="content">
                                <editor :path="path" v-model="form.content"></editor>
                            </form-item>
                            <form-item>
                                <i-button :loading="loading" type="primary" @click.native="submit">
                                    <span v-if="!loading">{{ trans('content.global.publish.submit') }}</span>
                                    <span v-else>{{ trans('content.global.publish.loading') }}</span>
                                </i-button>
                            </form-item>
                        </card>
                    </i-col>
                    <i-col span="8">
                        <card :bordered="false">
                            <form-item label="缩略图" prop="image">
                                <div class="ivu-upload-wrapper">
                                    <div class="preview" v-if="form.image">
                                        <img :src="form.image">
                                        <icon type="close" @click.native="removeImage"></icon>
                                    </div>
                                    <upload :action="action"
                                            :before-upload="uploadBefore"
                                            :format="['jpg','jpeg','png']"
                                            :headers="{
                                                    Authorization: `Bearer ${$store.state.token}`
                                                }"
                                            :max-size="2048"
                                            :on-error="uploadError"
                                            :on-format-error="uploadFormatError"
                                            :on-success="uploadSuccess"
                                            ref="upload"
                                            :show-upload-list="false"
                                            v-if="form.image === '' || form.image === null">
                                    </upload>
                                </div>
                            </form-item>
                            <form-item :label="trans('content.article.form.category.label')">
                                <cascader :data="categories" v-model="form.category" @on-change="categoryChanged"></cascader>
                            </form-item>
                            <form-item label="摘要">
                                <i-input placeholder="请输入文章简介"
                                         :rows="4"
                                         type="textarea"
                                         v-model="form.summery"></i-input>
                            </form-item>
                            <form-item :label="trans('content.article.form.sticky.label')" prop="sticky">
                                <i-switch v-model="form.is_sticky" size="large">
                                    <span slot="open">{{ trans('content.article.form.sticky.open') }}</span>
                                    <span slot="close">{{ trans('content.article.form.sticky.close') }}</span>
                                </i-switch>
                            </form-item>
                            <form-item :label="trans('content.article.form.hidden.label')" prop="hidden">
                                <i-switch v-model="form.is_hidden" size="large">
                                    <span slot="open">{{ trans('content.article.form.hidden.open') }}</span>
                                    <span slot="close">{{ trans('content.article.form.hidden.close') }}</span>
                                </i-switch>
                            </form-item>
                            <form-item :label="trans('content.article.form.date.label')">
                                <date-picker :placeholder="trans('content.article.form.date.placeholder')"
                                             v-model="form.created_at"
                                             type="datetime"></date-picker>
                            </form-item>
                            <form-item :label="trans('content.article.form.source.author.label')">
                                <i-input :placeholder="trans('content.article.form.source.author.placeholder')"
                                         v-model="form.source.author"></i-input>
                            </form-item>
                            <form-item :label="trans('content.article.form.source.link.label')">
                                <i-input :placeholder="trans('content.article.form.source.link.placeholder')"
                                         v-model="form.source.link"></i-input>
                            </form-item>
                            <template v-for="information in form.informations">
                                <form-item :label="information.name" :key="information.id" :prop="'informations.' + information.id + '.value'" :rules="form.informations[information.id].rules">
                                    <i-input v-if="information.type === 'input'" v-model="form.informations[information.id].value"></i-input>
                                    <i-input :rows="4" type="textarea"
                                             v-if="information.type === 'textarea'"
                                             v-model="form.informations[information.id].value"></i-input>
                                    <date-picker :type="information.type"
                                                 v-if="information.type === 'date' ||
                                                       information.type === 'daterange' ||
                                                       information.type === 'datetime'"
                                                 v-model="form.informations[information.id].value"></date-picker>
                                    <radio-group v-model="form.informations[information.id].value" size="large" v-if="information.type === 'radio'">
                                        <radio :label="option" v-for="option in information.opinions"></radio>
                                    </radio-group>
                                    <div class="ivu-upload-wrapper" v-if="information.type === 'picture'">
                                        <div class="preview" v-if="form.informations[information.id].value">
                                            <img :src="form.informations[information.id].value">
                                            <icon type="close" @click.native="remove(information.id)"></icon>
                                        </div>
                                        <upload :action="action"
                                                :data="{
                                                    id: information.id,
                                                    type: 'information'
                                                }"
                                                :format="['jpg','jpeg','png']"
                                                :headers="{
                                                    Authorization: `Bearer ${$store.state.token}`
                                                }"
                                                :max-size="2048"
                                                :on-error="uploadError"
                                                :on-format-error="uploadFormatError"
                                                :on-success="uploadSuccess"
                                                ref="upload"
                                                :show-upload-list="false"
                                                v-if="form.informations[information.id].value === '' || form.informations[information.id].value === null">
                                        </upload>
                                    </div>
                                    <p>{{ information.description }}</p>
                                </form-item>
                            </template>
                        </card>
                    </i-col>
                </row>
            </i-form>
        </div>
    </div>
</template>
