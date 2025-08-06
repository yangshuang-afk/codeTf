Vue.component('Add', {
    template: `
<el-dialog :close-on-click-modal="false" title="添加" width="95%" class="icon-dialog" :visible.sync="show" @open="open" :before-close="closeForm" append-to-body>
    <el-form :size="size" ref="form" :model="form" :rules="rules" :label-width="ismobile()?'90px':'101px'">
        <el-row >
            <el-col :span="24">
                <el-form-item label="日志内容" prop="rznr" >
                    <el-input  type="textarea" v-model="form.rznr" :autosize="{ minRows: 5, maxRows: 5}" clearable placeholder="请输入日志内容"></el-input>
                </el-form-item>
            </el-col>
        </el-row>    </el-form>
        
    <div slot="footer" class="dialog-footer">
        <el-button :size="size" :loading="loading" type="primary" @click="submit" >
            <span v-if="!loading">确 定</span>
            <span v-else>提 交 中...</span>
        </el-button>
        <el-button :size="size" @click="closeForm">取 消</el-button>
    </div>



</el-dialog>
    `
    ,
    components:{
        
    },
    props: {
        show: {
            type: Boolean,
            default: false
        },
        size: {
            type: String,
            default: 'small'
        },
    },
    data() {
        return {
            form: {
                rznr: '',
            },



            rules: {

            },
            loading: false
        };
    },
    watch:{


    },
    methods: {
        open() {

},
submit() {
    this.$refs['form'].validate(valid => {
        if (valid) {
            this.loading = true;

            axios.post(base_url + '/Wdrz/add', this.form)
                .then(res => {
                    if (res.data.status == 200) {
                        this.$message({message: res.data.msg, type: 'success'});
                        this.$emit('refesh_list');
                        this.closeForm();
                    } else {
                        this.loading = false;
                        this.$message.error(res.data.msg);
                    }
                })
                .catch(() => {
                    this.loading = false;
                });
        }
    });
},
normalizer(node) {
            if (node.children && !node.children.length) {
                delete node.children
            }
            return {
                id: node.val,
                label: node.key,
                children: node.children
            }
        },
closeForm() {
            this.$emit('update:show', false);
            this.loading = false;
            if (this.$refs['form'] !== undefined) {
                this.$refs['form'].resetFields();
            }
            
			
        }
    }
});