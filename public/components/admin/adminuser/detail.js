Vue.component('Detail', {
    template: `
        <el-dialog :close-on-click-modal="false"  title="" width="" class="icon-dialog" :visible.sync="show" @open="open" :before-close="closeForm" append-to-body>
            <div id="detail">
                <table cellpadding="0" cellspacing="0" class="table table-bordered detail-table" align="center" width="100%" style="word-break:break-all; margin-bottom:30px; font-size:13px;">
                    <tbody>
                        <tr>
    <td class="title" :width="ismobile()?'90px':'111px'">用户姓名：</td>
    <td >{{form.name}}</td>
</tr>
<tr>
    <td class="title" :width="ismobile()?'90px':'111px'">登录账号：</td>
    <td >{{form.user}}</td>
</tr>
<tr>
    <td class="title" :width="ismobile()?'90px':'111px'">所属分组：</td>
    <td >{{form.name}}</td>
</tr>
<tr>
    <td class="title" :width="ismobile()?'90px':'111px'">账号状态：</td>
    <td ><span v-if="form.status == '1'">正常</span><span v-if="form.status == '0'">禁用</span></td>
</tr>
<tr>
    <td class="title" :width="ismobile()?'90px':'111px'">用户备注：</td>
    <td >{{form.note}}</td>
</tr>
<tr>
    <td class="title" :width="ismobile()?'90px':'111px'">唯一登录：</td>
    <td >{{form.session_token}}</td>
</tr>
                    </tbody>
                </table>
                
            </div>
            
        </el-dialog>
    `,
	components:{
	},
    props: {
        show: {
            type: Boolean,
            default: true
        },
        size: {
            type: String,
            default: 'mini'
        },
        info: {
            type: Object
        },
    },
    data() {
        return {
            form: {},
            
        };
    },
    watch:{
		show(val){
			if(val){
				this.open()
			}
		}
	},
    methods: {
        open() {
            axios.post(base_url+'/Adminuser/detail', this.info)
                .then(res => {
                    this.form = res.data.data;
                });
        },
        
        closeForm() {
            this.$emit('update:show', false);
            
        }
    }
});