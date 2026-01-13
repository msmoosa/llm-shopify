<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Shopify App -->
    <!-- Polaris imports -->
    <meta name="shopify-api-key" content="{{ config('shopify-app.api_key') }}" />
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
    <script src="https://cdn.shopify.com/shopifycloud/polaris.js"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>


    <script>

    </script>
</head>

<body>
    <!-- === -->
    <!-- Home page pattern -->
    <!-- === -->
    <s-page id="app">

        <!-- === -->
        <!-- Callout Card -->
        <!-- If dismissed, use local storage or a database entry to avoid showing this section again to the same user. -->
        <!-- === -->
        <s-section id="main">
  <s-box style="max-width:50%; margin:0 auto;">
    
    <s-grid gridTemplateColumns="1fr auto" gap="small-400" alignItems="start">
      <s-grid
        gridTemplateColumns="@container (inline-size <= 480px) 1fr, auto auto"
        gap="base"
        alignItems="center"
      >
        <s-grid gap="small-100">
          <s-heading>Ready to boost your sales from ChatGPT?</s-heading>

          <s-paragraph>
            @{{ message }}
          </s-paragraph>

          <s-stack direction="inline" gap="small-200">
            <s-button variant="primary" :loading="isLoading" @click="generate()">
              Generate
            </s-button>
            <s-button variant="neutral">Learn more</s-button>
          </s-stack>
        </s-grid>

        <s-box maxInlineSize="400px" borderRadius="base" overflow="hidden">
          <s-image
            src="/images/boostsales.jpg"
            alt="Customize checkout illustration"
            aspectRatio="1/0.5"
          ></s-image>
        </s-box>
      </s-grid>
    </s-grid>

  </s-box>
</s-section>
    </s-page>
</body>

<script src="/js/welcome.js"></script>
<script>
    const { createApp, ref } = Vue

  createApp({
    data() {
      return {
        state: 'init',
        message: 'Start by generating your LLMs.txt file that helps chat bots discover your website and products.'
      }
    },
    computed: {
        isLoading() {
            return this.state == 'loading'
        }
    },
    methods: {
        async generate() {
            this.message = 'Generating...';
            this.state = 'loading';

            try {
                const response = await fetch('/api/generate', {
                    method: 'GET',
                    headers: {
                        'Accept': 'text/markdown',
                    },
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const markdown = await response.text();

                // For now, just log the markdown and show a success message.
                console.log(markdown);
                this.message = 'Your LLMs.txt is generated! ChatGPT and other chat tools can now discover products';
                this.state = 'generated';
            } catch (error) {
                console.error(error);
                this.message = 'Something went wrong while generating.';
            }
        }
    }
  }).mount('#app')
</script>

</html>