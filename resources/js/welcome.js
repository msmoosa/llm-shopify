const { createApp, ref } = Vue

  createApp({
    data() {
      return {
        state: 'init',
        message: 'Hello world'
      }
    },
    methods: {
        async greet() {
            this.message = 'Generating...';

            try {
            const response = await fetch('/api/generate-llms', {
                method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({}),
            });

            if (!response.ok) {
                throw new Error('Request failed');
            }

            const data = await response.json();
            this.message = data.message ?? 'LLMs.txt generated!';
            } catch (error) {
            console.error(error);
            this.message = 'Something went wrong while generating.';
            }
        }
    }
  }).mount('#app')