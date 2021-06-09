<?php

namespace Woren951\MetaTags\Managers;

class MetaTags
{
    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var array
     */
    protected $links = [];

    /**
     * @var array
     */
    protected $og = [];

    /**
     * @var array
     */
    protected $twitter = [];

    /**
     * @var array
     */
    protected $jsonLd = [];

    /**
     * @param string $value
     * @param array|null $providers
     * @return $this
     */
    public function title(string $value, array $providers = []): self
    {
        if (empty($providers)) {
            $providers = [
                'meta',
                'og',
                'twitter',
            ];
        }

        if (in_array('meta', $providers)) {
            $this->meta('title', 'title', [], $value);
        }

        if (in_array('og', $providers)) {
            $this->og('title', $value);
        }

        if (in_array('twitter', $providers)) {
            $this->twitter('title', $value);
        }

        return $this;
    }

    /**
     * @param string $value
     * @param array|null $providers
     * @return $this
     */
    public function description(string $value, array $providers = []): self
    {
        if (empty($providers)) {
            $providers = [
                'meta',
                'og',
                'twitter',
            ];
        }

        if (in_array('meta', $providers)) {
            $this->meta('description', 'meta', [
                'content' => $value
            ]);
        }

        if (in_array('og', $providers)) {
            $this->og('description', $value);
        }

        if (in_array('twitter', $providers)) {
            $this->twitter('description', $value);
        }

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function canonical(string $value): self
    {
        $this->replaceLink('canonical', $value, []);

        $this->og('url', $value);

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function robots(string $value): self
    {
        $this->meta('robots', 'meta', [
            'content' => $value,
        ]);

        return $this;
    }

    /**
     * @param string $value
     * @param int|null $width
     * @param int|null $heigth
     * @param string|null $type
     * @return $this
     */
    public function image(
        string $value,
        int $width = null,
        int $heigth = null,
        string $type = null
    ): self {
        $this->og('image', $value);

        if ($width) {
            $this->og('image:width', $width);
        }

        if ($heigth) {
            $this->og('image:heigth', $heigth);
        }

        if ($type) {
            $this->og('image:type', $type);
        }

        $this->twitter('image', $value);

        return $this;
    }

    /**
     * @param string $key
     * @param string $tag
     * @param array|null $attributes
     * @param string|null $content
     * @return $this
     */
    public function meta(string $key, string $tag, array $attributes = [], string $content = null): self
    {
        $this->meta[$key] = [
            'tag' => $tag,
            'attributes' => $attributes,
            'content' => $content,
        ];

        return $this;
    }

    /**
     * @param string $rel
     * @param string $href
     * @param array $attributes
     * @return $this
     */
    public function addLink(string $rel, string $href, array $attributes): self
    {
        $this->links[] = [
            'rel' => $rel,
            'href' => $href,
            'attributes' => $attributes,
        ];

        return $this;
    }

    /**
     * @param string $rel
     * @param string $href
     * @param array $attributes
     * @return $this
     */
    public function replaceLink(string $rel, string $href, array $attributes): self
    {
        foreach($this->links as $key => $link){
            if ($link['rel'] === $rel) {
                unset($this->links[$key]);
            }
        }

        $this->links[] = [
            'rel' => $rel,
            'href' => $href,
            'attributes' => $attributes,
        ];

        return $this;
    }

    /**
     * @param string $key
     * @param string $content
     * @return $this
     */
    public function og(string $key, string $content): self
    {
        $this->og["og:{$key}"] = [
            'tag' => 'meta',
            'attributes' => [
                'content' => $content,
            ],
            'content' => null,
        ];

        return $this;
    }

    /**
     * @param string $key
     * @param string $content
     * @return $this
     */
    public function twitter(string $key, string $content): self
    {
        $this->twitter["twitter:{$key}"] = [
            'tag' => 'meta',
            'attributes' => [
                'content' => $content,
            ],
            'content' => null,
        ];

        return $this;
    }

    /**
     * @param array $schema
     * @return $this
     */
    public function jsonld(array $schema): self
    {
        $this->jsonLd[] = $schema;

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $meta = '';

        foreach ($this->meta as $key => $value) {
            $attributes = [];

            $attributes = array_merge(
                [ 'name' => $key ],
                $value['attributes'] ?? []
            );

            $meta .= $this->createTag($value['tag'], $attributes, $value['content'] ?? null) . "\n";
        }

        foreach ($this->links as $key => $value) {
            $attributes = array_merge(
                [
                    'rel' => $value['rel'],
                    'href' => $value['href'],
                ],
                $value['attributes'] ?? []
            );

            $meta .= $this->createTag('link', $attributes, null) . "\n";
        }

        foreach ($this->og as $key => $value) {
            $attributes = array_merge(
                [ 'name' => $key ],
                $value['attributes'] ?? []
            );

            $meta .= $this->createTag($value['tag'], $attributes, $value['content'] ?? null) . "\n";
        }

        foreach ($this->twitter as $key => $value) {
            $attributes = array_merge(
                [ 'name' => $key ],
                $value['attributes'] ?? []
            );

            $meta .= $this->createTag($value['tag'], $attributes, $value['content'] ?? null) . "\n";
        }

        foreach ($this->jsonLd as $schema) {
            $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

            $meta .= "<script type=\"application/ld+json\">\n{$json}\n</script>";
        }

        return $meta;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getMeta(string $key, $default = null)
    {
        $tag = $this->meta[$key]['tag'] ?? null;

        if ($tag === 'title') {
            return $this->meta[$key]['content'] ?? $default;
        } elseif ($tag === 'meta') {
            return $this->meta[$key]['attributes']['content'] ?? $default;
        } elseif ($tag === 'link') {
            return $this->meta[$key]['attributes']['href'] ?? $default;
        }

        return $default;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOg(string $key, $default = null)
    {
        return $this->og[$key]['attributes']['content'] ?? $default;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getTwitter(string $key, $default = null)
    {
        return $this->twitter[$key]['attributes']['content'] ?? $default;
    }

    /**
     * @param string $tag
     * @param array|null $attributes
     * @param string|null $content
     * @return string
     */
    protected function createTag(string $tag, array $attributes = [], string $content = null): string
    {
        $element = "<{$tag}";

        foreach ($attributes as $key => $value) {
            $element .= " {$key}=\"{$this->escapeAll($value)}\"";
        }

        if ($content !== null) {
            return "$element>{$content}</{$tag}>";
        }

        return "$element>";
    }

    /**
     * @param string $value
     * @return string
     */
    protected function escapeAll(string $value): string
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8');
    }
}
