Vue.component('Resetpwd', {
    template: `
<el-dialog :close-on-click-modal="false" title="重置密码" width="85%" class="icon-dialog" :visible.sync="show" @open="open" :before-close="closeForm" append-to-body>
        
        <el-form :size="size" ref="form" :model="form" :rules="rules" :label-width="ismobile()?'90px':'101px'">
            
        </el-form>
        
			<div slot="footer" class="dialog-footer">
				<el-button :size="size" :loading="loading" type="primary" @click="submit" >
					<span v-if="!loading">确 定</span>
					<span v-else>提 交 中...</span>
				</el-button>
				<el-button :size="size" @click="closeForm">取 消</el-button>
			</div>

        
        
</el-dialog>
    `,
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
        info: {
            type: Object
        },
    },
    data() {
        return {
            form: {
            user_id: '',
            name: '',
            user: '',
            pwd: '',
            name: '',
            role_id: '',
            status: 1,
            note: '',
            create_time: '',
            session_token: '',
        },
role_ids: [],

activeName: '',
rules: {
name: [
{required: true, message: '用户姓名不能为空', trigger: 'blur'}
],
user: [
{required: true, message: '登录账号不能为空', trigger: 'blur'}
],
pwd: [
{required: true, message: '用户密码不能为空', trigger: 'blur'}
],
role_id: [
{required: true, message: '所属角色不能为空', trigger: 'change'}
],
status: [
{required: true, message: '账号状态不能为空', trigger: 'change'}
]
        },
loading: false
        };
    },
                            watch:{
                            
                 show(val){
                                if(val){
                                    axios.post(base_url + '/Adminuser/getFieldList').then(res => {
                                        if(res.data.status == 200){
                                           this.role_ids = res.data.data?.role_ids || [] 

                                        }
                                    })
                                }
                            },
                            
                            
                        },
    methods: {
        open() {
    this.form = this.info
this.form.create_time = parseTime(this.form.create_time)
},
submit() {
    this.$refs['form'].validate(valid => {
        if (valid) {
            this.loading = true;

            axios.post(base_url + '/Adminuser/resetPwd', this.form)
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