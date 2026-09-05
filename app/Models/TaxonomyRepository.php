<?php
/**
 * Категории и теги.
 *
 * Категория у статьи одна, тегов может быть сколько угодно — на этом
 * построены оба фильтра в разделе.
 */

declare(strict_types=1);

namespace App\Models;

final class TaxonomyRepository extends Repository
{
    /**
     * Категории со счётчиком опубликованных статей.
     *
     * @return array<int, array<string, mixed>>
     */
    public function categories(): array
    {
        return $this->all(
            // updated_at — дата самой свежей статьи в категории. Нужна карте
            // сайта: сама категория не меняется, меняется её состав.
            "SELECT c.id, c.slug, c.name, c.title, c.description, c.position,
                    COUNT(a.id) AS articles,
                    MAX(a.updated_at) AS updated_at
               FROM categories c
               LEFT JOIN articles a
                      ON a.category_id = c.id AND a.status = 'published'
              GROUP BY c.id, c.slug, c.name, c.title, c.description, c.position
              ORDER BY c.position, c.name"
        );
    }

    /** @return array<string, mixed>|null */
    public function category(string $slug): ?array
    {
        return $this->one('SELECT * FROM categories WHERE slug = :slug', ['slug' => $slug]);
    }

    /**
     * Теги со счётчиком статей.
     *
     * Теги без единой статьи в фильтре не показываем: выбор, который
     * гарантированно даёт пустой список, посетителю не нужен.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tags(bool $onlyUsed = false): array
    {
        $having = $onlyUsed ? 'HAVING articles > 0' : '';

        return $this->all(
            "SELECT t.id, t.slug, t.name, COUNT(a.id) AS articles
               FROM tags t
               LEFT JOIN article_tag at ON at.tag_id = t.id
               LEFT JOIN articles a
                      ON a.id = at.article_id AND a.status = 'published'
              GROUP BY t.id, t.slug, t.name, t.position
              {$having}
              ORDER BY t.position, t.name"
        );
    }

    /** @return array<string, mixed>|null */
    public function tag(string $slug): ?array
    {
        return $this->one('SELECT * FROM tags WHERE slug = :slug', ['slug' => $slug]);
    }

    /** @return array<int, int> Идентификаторы существующих тегов */
    public function tagIds(array $slugs): array
    {
        return array_map(
            static fn (array $tag): int => (int) $tag['id'],
            $this->tagsBySlugs($slugs),
        );
    }

    public function addTag(string $slug, string $name, int $position = 0): void
    {
        $this->run(
            'INSERT INTO tags (slug, name, position) VALUES (:slug, :name, :position)
             ON DUPLICATE KEY UPDATE name = VALUES(name), position = VALUES(position)',
            ['slug' => $slug, 'name' => $name, 'position' => $position],
        );
    }

    public function renameTag(int $id, string $name, int $position): void
    {
        $this->run(
            'UPDATE tags SET name = :name, position = :position WHERE id = :id',
            ['name' => $name, 'position' => $position, 'id' => $id],
        );
    }

    /** Удаляет тег. Связи со статьями снимет сама база. */
    public function deleteTag(int $id): void
    {
        $this->run('DELETE FROM tags WHERE id = :id', ['id' => $id]);
    }

    /**
     * Оставляет из списка только существующие теги, сохраняя порядок
     * из настроек. Нужно фильтру: в адресе может быть что угодно.
     *
     * @param  array<int, string> $slugs
     * @return array<int, array<string, mixed>>
     */
    public function tagsBySlugs(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        $in     = implode(', ', array_fill(0, count($slugs), '?'));
        $tags   = $this->all(
            "SELECT id, slug, name FROM tags WHERE slug IN ({$in}) ORDER BY position, name",
            array_values($slugs),
        );

        return $tags;
    }
}
