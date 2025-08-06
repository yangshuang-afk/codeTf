Vue.component('Detail', {
    template: `
        <el-dialog :close-on-click-modal="false"  title="" width="" class="icon-dialog" :visible.sync="show" @open="open" :before-close="closeForm" append-to-body>
            <div id="detail">
                <table cellpadding="0" cellspacing="0" class="table table-bordered detail-table" align="center" width="100%" style="word-break:break-all; margin-bottom:30px; font-size:13px;">
                    <tbody>
                        <tr>
    <td class="title" :width="ismobile()?'90px':'111px'">日志内容：</td>
    <td style="white-space: pre-wrap;">{{form.rznr}}</td>
</tr>
                    </tbody>
                </table>
                
            </div>

        </el-dialog>
    `
    ,
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
            axios.post(base_url+'/Wdrz/detail', this.info)
                .then(res => {
                    this.form = res.data.data;
                });
        },
        
        closeForm() {
            this.$emit('update:show', false);
            
        }
    }
});