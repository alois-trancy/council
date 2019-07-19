<script>
	import Replies from '../components/Replies.vue';
	import SubscribeButton from '../components/SubscribeButton.vue';	
	import Highlight from '../components/Highlight.vue';

	export default {

		props: [
			'thread'
		],

		components: {
			Replies,
			SubscribeButton,
			Highlight,
		},

		data() {
			return {
				repliesCount: this.thread.replies_count,
				title: this.thread.title,
				body: this.thread.body,
				locked: this.thread.locked,
				pinned: this.thread.pinned,
				editing: false,
				form: {},
			};
		},

		created() {
			this.resetForm();
		},      

		methods: {
			toggleLock() {
				axios[this.locked ? 'delete' : 'post']('/locked-threads/' + this.thread.slug);
				this.locked = ! this.locked;
			},

			cancel() {
				this.resetForm();
			},

			togglePin() {
                let uri = `/pinned-threads/${this.thread.slug}`;
                axios[this.pinned ? 'delete' : 'post'](uri);
                this.pinned = ! this.pinned;
            },

			update() {
				let uri = `/threads/${this.thread.channel.slug}/${this.thread.slug}`;
				axios.patch(uri, this.form).then(() => {
					this.editing = false;
					this.title = this.form.title;
					this.body = this.form.body;
					flash('Your thread has been updated.');
				});
			},

			resetForm() {
				this.form = {
					title: this.thread.title,
					body: this.thread.body,
				};

				this.editing = false;
			},

            classes(target) {
                return [
                    'btn',
                    target ? 'btn-primary' : 'btn-default'
                ];
            },
		}
		
	}
</script>