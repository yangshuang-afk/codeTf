Vue.component('Update', {
	template: `
		<el-dialog title="修改" width="600px" class="icon-dialog" :visible.sync="show" @open="open" :before-close="closeForm" append-to-body>
			<el-form :size="size" ref="form" :model="form" :rules="rules" :label-width=" ismobile()?'90px':'16%'">
				<el-row >
					<el-col :span="24">
						<el-form-item label="action_id" prop="action_id">
							<el-input  v-model="form.action_id" autoComplete="off" clearable  placeholder="请输入action_id"></el-input>
						</el-form-item>
					</el-col>
				</el-row>
				<el-row >
					<el-col :span="24">
						<el-form-item label="代码内容" prop="content">
							<el-input  type="textarea" autoComplete="off" v-model="form.content"  :autosize="{ minRows: 2, maxRows: 4}" clearable placeholder="请输入代码内容"/>
						</el-form-item>
					</el-col>
				</el-row>
				<el-row >
					<el-col :span="24">
						<el-form-item label="功能描述" prop="description">
							<el-input  type="textarea" autoComplete="off" v-model="form.description"  :autosize="{ minRows: 2, maxRows: 4}" clearable placeholder="请输入功能描述"/>
						</el-form-item>
					</el-col>
				</el-row>
				<el-row >
					<el-col :span="24">
						<el-form-item label="创建时间" prop="create_time">
							<el-date-picker value-format="yyyy-MM-dd HH:mm:ss" type="datetime" v-model="form.create_time" clearable placeholder="请输入创建时间"></el-date-picker>
						</el-form-item>
					</el-col>
				</el-row>
				<el-row >
					<el-col :span="24">
						<el-form-item label="所属菜单" prop="menu_id">
							<el-input  v-model="form.menu_id" autoComplete="off" clearable  placeholder="请输入所属菜单"></el-input>
						</el-form-item>
					</el-col>
				</el-row>
			</el-form>
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
		info: {
			type: Object,
		},
	},
	data(){
		return {
			form: {
				action_id:'',
				content:'',
				description:'',
				create_time:'',
				menu_id:'',
			},
			loading:false,
			rules: {
				action_id:[
					{pattern:/^[0-9]*$/, message: 'action_id格式错误'}
				],
				content:[
					{required: true, message: '代码内容不能为空', trigger: 'blur'},
				],
				description:[
					{required: true, message: '功能描述不能为空', trigger: 'blur'},
				],
				menu_id:[
					{pattern:/^[0-9]*$/, message: '所属菜单格式错误'}
				],
			}
		}
	},
	methods: {
		open(){
			this.form = this.info
			this.form.create_time = parseTime(this.form.create_time)
		},
		submit(){
			this.$refs['form'].validate(valid => {
				if(valid) {
					this.loading = true
					axios.post(base_url + '/Actionremarks/update',this.form).then(res => {
						if(res.data.status == 200){
							this.$message({message: res.data.msg, type: 'success'})
							this.$emit('refesh_list')
							this.closeForm()
						}else{
							this.loading = false
							this.$message.error(res.data.msg)
						}
					}).catch(()=>{
						this.loading = false
					})
				}
			})
		},
		closeForm(){
			this.$emit('update:show', false)
			this.loading = false
			if (this.$refs['form']!==undefined) {
				this.$refs['form'].resetFields()
			}
		},
	}
})
