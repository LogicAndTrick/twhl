<?php

namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Comments\CommentController;
use App\Http\Controllers\Controller;
use App\Models\Accounts\ApiKey;
use App\Models\Accounts\Permission;
use App\Models\Accounts\User;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentMeta;
use App\Models\Competitions\CompetitionJudgeType;
use App\Models\Competitions\CompetitionRestriction;
use App\Models\Competitions\CompetitionRestrictionGroup;
use App\Models\Competitions\CompetitionStatus;
use App\Models\Competitions\CompetitionType;
use App\Models\Engine;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumPost;
use App\Models\Forums\ForumThread;
use App\Models\Game;
use App\Models\License;
use App\Models\Shout;
use App\Models\Vault\VaultCategory;
use App\Models\Vault\VaultInclude;
use App\Models\Vault\VaultItem;
use App\Models\Vault\VaultScreenshot;
use App\Models\Vault\VaultType;
use App\Models\Wiki\WikiObject;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\ValidationException;
use Input;
use Request;
use Auth;
use DB;

class ApiController extends Controller {

    private $descriptors = [
        'engines' => [
            'description' => 'Game Engines',
            'expand' => [],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [],
            'object' => Engine::class,
            'filter_columns' => ['name'],
            'sort_column' => 'orderindex',
            'default_filters' => []
        ],
        'games' => [
            'description' => 'Games',
            'expand' => ['engine'],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [
                'get' => [
                    'engine_id' => [ 'type' => 'integer', 'description' => 'The ID of the engine' ],
                ],
            ],
            'object' => Game::class,
            'filter_columns' => ['name'],
            'sort_column' => 'orderindex',
            'default_filters' => []
        ],
        'licenses' => [
            'description' => 'Content Licenses',
            'expand' => [],
            'methods' => ['get'],
            'auth' => [],
            'object' => License::class,
            'filter_columns' => ['name'],
            'sort_column' => 'orderindex',
            'default_filters' => []
        ],
        'posts' => [
            'description' => 'Forum Posts',
            'expand' => ['forum', 'thread', 'user'],
            'methods' => [ 'get', 'post', 'put' ],
            'auth' => [
                'post' => 'ForumCreate',
                'put' => 'ForumCreate'
            ],
            'parameters' => [
                'get' => [
                    'forum_id' => [ 'type' => 'integer', 'description' => 'The ID of the forum' ],
                    'thread_id' => [ 'type' => 'integer', 'description' => 'The ID of the thread' ],
                    'user_id' => [ 'type' => 'integer', 'description' => 'The ID of the user' ],
                ],
                'post' => [
                    'thread_id' => [ 'required' => true, 'type' => 'integer', 'description' => 'The ID of the thread to post in' ],
                    'content_text' => [ 'required' => true, 'type' => 'string', 'description' => 'The text of the forum post to create']
                ],
                'put' => [
                    'id' => [ 'required' => true, 'type' => 'integer', 'description' => 'The ID of the forum post to edit' ],
                    'content_text' => [ 'required' => true, 'type' => 'string', 'description' => 'The updated text of the forum post']
                ]
            ],
            'object' => ForumPost::class,
            'filter_columns' => ['content_text'],
            'sort_column' => 'updated_at',
            'default_filters' => [],
            'additional_methods' => [
                'format' => [
                    'method' => 'post',
                    'operationId' => 'formatPost',
                    'parameters' => [
                        'field' => [ 'required' => false, 'type' => 'string', 'description' => 'If set, the specified field in the post body will be used instead of `text`' ],
                        'text' => [ 'required' => true, 'type' => 'string', 'description' => 'The TWHL WikiCode text to turn into HTML' ]
                    ],
                    'response' => [
                        'description' => 'HTML string',
                        'schema' => [
                            'type' => 'string'
                        ]
                    ]
                ]
            ]
        ],
        'threads' => [
            'description' => 'Forum Threads',
            'expand' => ['forum', 'user', 'last_post', 'last_post.user'],
            'methods' => ['get', 'post'],
            'auth' => [
                'post' => 'ForumCreate'
            ],
            'parameters' => [
                'get' => [
                    'forum_id' => [ 'type' => 'integer', 'description' => 'The ID of the forum' ],
                    'user_id' => [ 'type' => 'integer', 'description' => 'The ID of the user' ],
                ],
                'post' => [
                    'forum_id' => [ 'required' => true, 'type' => 'integer', 'description' => 'The ID of the forum to post in' ],
                    'title' => [ 'required' => true, 'type' => 'string', 'description' => 'The title of the thread to create' ],
                    'content_text' => [ 'required' => true, 'type' => 'string', 'description' => 'The text of the first post in the thread' ]
                ]
            ],
            'object' => ForumThread::class,
            'filter_columns' => ['title'],
            'sort_column' => 'updated_at',
            'default_filters' => []
        ],
        'forums' => [
            'description' => 'Forums',
            'expand' => ['last_post', 'last_post.thread', 'last_post.user'],
            'methods' => ['get'],
            'auth' => [],
            'object' => Forum::class,
            'filter_columns' => ['name'],
            'sort_column' => 'order_index',
            'default_filters' => []
        ],
        'users' => [
            'description' => 'Users',
            'expand' => [],
            'methods' => ['get'],
            'auth' => [],
            'object' => User::class,
            'filter_columns' => ['name'],
            'sort_column' => 'id',
            'default_filters' => []
        ],
        'permissions' => [
            'description' => 'User Permissions',
            'expand' => [],
            'methods' => ['get'],
            'auth' => [],
            'object' => Permission::class,
            'filter_columns' => ['name'],
            'sort_column' => 'name',
            'default_filters' => []
        ],
        'comments' => [
            'description' => 'Comments',
            'expand' => ['comment_metas', 'user'],
            'methods' => [ 'get', 'post', 'put' ],
            'auth' => [
                'post' => 'NewsComment, VaultComment, JournalComment, PollComment, WikiComment',
                'put' => 'NewsComment, VaultComment, JournalComment, PollComment, WikiComment'
            ],
            'parameters' => [
                'get' => [
                    'article_type' => [ 'type' => 'string', 'enum' => ['n','j','v','p','w'], 'description' => 'The article type ([n]ews, [j]ournal, [v]ault, [p]oll, [w]iki)' ],
                    'article_id' => [ 'type' => 'integer', 'description' => 'The ID of the article' ],
                    'user_id' => [ 'type' => 'integer', 'description' => 'The ID of the user' ],
                ],
                'post' => [
                    'article_type' => [ 'required' => true, 'type' => 'string', 'enum' => ['n','j','v','p','w'], 'description' => 'The article type to add a comment to ([n]ews, [j]ournal, [v]ault, [p]oll, [w]iki)' ],
                    'article_id' => [ 'required' => true, 'type' => 'integer', 'description' => 'The ID of the article to comment in' ],
                    'content_text' => [ 'required' => true, 'type' => 'string', 'description' => 'The text of the comment to create'],
                    'meta_rating' => [ 'required' => false, 'type' => 'integer', 'description' => '[Vault only] If supported, the rating attached to the comment.'],
                ],
                'put' => [
                    'id' => [ 'required' => true, 'type' => 'integer', 'description' => 'The ID of the comment to edit' ],
                    'content_text' => [ 'required' => true, 'type' => 'string', 'description' => 'The updated text of the comment'],
                    'meta_rating' => [ 'required' => false, 'type' => 'integer', 'description' => '[Vault only] If supported, the rating attached to the comment.'],
                ]
            ],
            'object' => Comment::class,
            'filter_columns' => ['content_text'],
            'sort_column' => 'updated_at',
            'default_filters' => []
        ],
        'comment-metas' => [
            'description' => 'Comment Metas',
            'methods' => [],
            'object' => CommentMeta::class,
            'filter_columns' => [],
        ],
        'wiki-revisions' => [
            'description' => 'Wiki Revisions',
            'expand' => ['wiki_object', 'wiki_object.permission', 'wiki_object.current_revision', 'user', 'wiki_revision_metas'],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [
                'get' => [
                    'is_active' => [ 'type' => 'integer', 'enum' => [0,1], 'description' => 'Allows to filter for only active or inactive revisions' ],
                    'object_id' => [ 'type' => 'integer', 'description' => 'The ID of the parent wiki object' ],
                    'user_id' => [ 'type' => 'integer', 'description' => 'The ID of the user' ],
                ]
            ],
            'object' => WikiRevision::class,
            'filter_columns' => ['title', 'content_text'],
            'sort_column' => 'title',
            'allowed_sort_columns' => ['title','created_at'],
            'default_filters' => []
        ],
        'wiki-objects' => [
            'description' => 'Wiki Objects',
            'methods' => [],
            'object' => WikiObject::class,
            'filter_columns' => [],
        ],
        'wiki-revision-metas' => [
            'description' => 'Wiki Revision Metas',
            'expand' => [],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [
                'get' => [
                    'revision_id' => [ 'type' => 'integer', 'description' => 'The ID of the wiki revision' ],
                    'key' => [ 'type' => 'string', 'enum' => ['c','l','w','h','s','u'], 'description' => 'The metadata key ([c]ategory, [l]ink, image [w]idth, image [h]eight, file [s]ize, [u]pload id)' ],
                    'value' => [ 'type' => 'string', 'description' => 'The metadata value']
                ]
            ],
            'object' => WikiRevisionMeta::class,
            'filter_columns' => [],
            'sort_column' => 'id',
            'allowed_sort_columns' => [],
            'default_filters' => []
        ],
        'vault-categories' => [
            'description' => 'Vault Categories',
            'expand' => [],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [],
            'object' => VaultCategory::class,
            'filter_columns' => ['name'],
            'sort_column' => 'orderindex',
            'allowed_sort_columns' => [],
            'default_filters' => []
        ],
        'vault-types' => [
            'description' => 'Vault Types',
            'expand' => [],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [],
            'object' => VaultType::class,
            'filter_columns' => ['name'],
            'sort_column' => 'orderindex',
            'allowed_sort_columns' => [],
            'default_filters' => []
        ],
        'vault-includes' => [
            'description' => 'Vault Includes',
            'expand' => ['vault_type'],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [],
            'object' => VaultInclude::class,
            'filter_columns' => ['name'],
            'sort_column' => 'orderindex',
            'allowed_sort_columns' => [],
            'default_filters' => []
        ],
        'vault-screenshots' => [
            'description' => 'Vault Screenshots',
            'expand' => ['vault_item'],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [
                'get' => [
                    'item_id' => [ 'type' => 'integer', 'description' => 'The ID of the vault item' ],
                    'is_primary' => [ 'type' => 'integer', 'enum' => [0,1], 'description' => 'Filter for the primary (first) screenshot' ]
                ]
            ],
            'object' => VaultScreenshot::class,
            'filter_columns' => [],
            'sort_column' => 'order_index',
            'allowed_sort_columns' => [],
            'default_filters' => []
        ],
        'vault-items' => [
            'description' => 'Vault Items',
            'methods' => [],
            'object' => VaultItem::class,
            'filter_columns' => [],
        ],
        'competition-restriction-groups' => [
            'description' => 'Competition Restriction Groups',
            'expand' => [],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [],
            'object' => CompetitionRestrictionGroup::class,
            'filter_columns' => ['title'],
            'sort_column' => 'id',
            'allowed_sort_columns' => [],
            'default_filters' => []
        ],
        'competition-restrictions' => [
            'description' => 'Competition Restrictions',
            'expand' => ['group'],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [
                'get' => [
                    'group_id' => [ 'type' => 'integer', 'description' => 'The ID of the restriction group' ],
                ]
            ],
            'object' => CompetitionRestriction::class,
            'filter_columns' => ['content_text'],
            'sort_column' => 'id',
            'allowed_sort_columns' => [],
            'default_filters' => []
        ],
        'competition-statuses' => [
            'description' => 'Competition Statuses',
            'expand' => [],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [],
            'object' => CompetitionStatus::class,
            'filter_columns' => ['name'],
            'sort_column' => 'id',
            'allowed_sort_columns' => [],
            'default_filters' => []
        ],
        'competition-types' => [
            'description' => 'Competition Types',
            'expand' => [],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [],
            'object' => CompetitionType::class,
            'filter_columns' => ['name'],
            'sort_column' => 'id',
            'allowed_sort_columns' => [],
            'default_filters' => []
        ],
        'competition-judge-types' => [
            'description' => 'Competition Judge Types',
            'expand' => [],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [],
            'object' => CompetitionJudgeType::class,
            'filter_columns' => ['name'],
            'sort_column' => 'id',
            'allowed_sort_columns' => [],
            'default_filters' => []
        ],
        'shouts' => [
            'description' => 'Shouts',
            'expand' => ['user'],
            'methods' => ['get', 'post', 'put', 'delete'],
            'auth' => [
                'post' => '',
                'put' => 'ForumAdmin',
                'delete' => 'ForumAdmin'
            ],
            'parameters' => [
                'get' => [
                    'user_id' => [ 'type' => 'integer', 'description' => 'The ID of the user' ],
                ],
                'post' => [
                    'text' => [ 'required' => true, 'type' => 'string', 'description' => 'The shout text' ]
                ],
                'put' => [
                    'id' => [ 'required' => true, 'type' => 'integer', 'description' => 'The ID of the shout to edit' ],
                    'text' => [ 'required' => true, 'type' => 'string', 'description' => 'The updated text of the shout']
                ],
                'delete' => [
                    'id' => [ 'required' => true, 'type' => 'integer', 'description' => 'The ID of the shout to delete' ]
                ]
            ],
            'object' => Shout::class,
            'filter_columns' => ['content'],
            'sort_column' => 'created_at',
            'sort_descending' => true,
            'default_filters' => [],
            'additional_methods' => [
                'from' => [
                    'method' => 'get',
                    'operationId' => 'getShoutsCreatedFrom',
                    'parameters' => [
                        'timestamp' => [ 'required' => false, 'type' => 'integer', 'description' => 'The timestamp to retrieve shouts posted after' ]
                    ],
                    'response' => [
                        'description' => 'Recent shouts',
                        'schema' => [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/definitions/Shout'
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'api-key' => [
            'description' => 'Api Keys',
            'expand' => [],
            'methods' => ['post'],
            'auth' => [],
            'parameters' => [
                'post' => [
                    'username' => [ 'required' => true, 'type' => 'string', 'description' => 'The username to log in with' ],
                    'password' => [ 'required' => true, 'type' => 'string', 'format' => 'password', 'description' => 'The password to log in with' ],
                    'app' => [ 'required' => true, 'type' => 'string', 'description' => 'The key context (what application is using this key?)' ],
                ]
            ],
            'allow_unauthenticated' => true,
            'object' => ApiKey::class,
            'filter_columns' => ['app'],
            'sort_column' => 'id',
            'allowed_sort_columns' => [],
            'default_filters' => []
        ]
    ];

    private function getRelatedClass($obj, $prop)
    {
        $rel = null;
        $o = $obj;
        $spl = explode('.', $prop);
        for ($i = 0; $i < count($spl); $i++) {
            $name = $spl[$i];
            if (!method_exists($o, $name)) return null;
            $rel = $o->$name();
            if (!$rel instanceof Relation) return null;
            $o = $rel->getRelated();
        }

        if ($rel && $o) {
            $rel_expl = explode('\\', get_class($o));
            $rel_name = $rel_expl[count($rel_expl) - 1];
            $type = "#/definitions/$rel_name";
            if ($rel instanceof HasMany) {
                $type = 'array';
            }
            return [
                'ref' => "#/definitions/$rel_name",
                'type' => $type
            ];
        }

        return null;
    }

    private function getDefinitions()
    {
        $defs = [];

        foreach ($this->descriptors as $key => $desc) {
            $obj = new $desc['object'];
            $expl = explode('\\', $desc['object']);
            $name = $expl[count($expl) - 1];
            $props = [];
            foreach ($obj->visible as $prop) {

                $props[$prop] = [];

                $type = 'string';
                $type_key = 'type';
                if ($prop == 'id' || $prop == 'orderindex' || $prop == 'order_index' || substr($prop, -3) == '_id') {
                    $type = 'integer';
                }
                else if ($prop == 'created_at' || $prop == 'updated_at' || $prop == 'deleted_at' || ($obj->dates && array_search($prop, $obj->dates) !== false)) {
                    $props[$prop]['format'] = 'date-time';
                }
                else {
                    $rel = $this->getRelatedClass($obj, $prop);
                    if ($rel) {
                        $type = $rel['type'];
                        if ($type == 'array') {
                            $props[$prop]['items'] = [ '$ref' => $rel['ref'] ];
                        } else {
                            $type_key = '$ref';
                        }
                    }
                }
                $props[$prop][$type_key] = $type;
            }
            $defs[$name] = [
                'type' => 'object',
                'properties' => $props
            ];
        }

        return $defs;
    }

    private function getPathItems($key, $desc)
    {
        $items = [];
        $items["/$key"] = [];

        $expl = explode('\\', $desc['object']);
        $name = $expl[count($expl) - 1];
        $filterCols = implode(', ', $desc['filter_columns']);

        foreach ($desc['methods'] as $method) {
            $auth = isset($desc['auth'][$method]) ? $desc['auth'][$method] : null;
            $params = isset($desc['parameters'][$method]) ? $desc['parameters'][$method] : [];
            $pars = [];
            $reqr = [];
            foreach ($params as $k => $v) {
                if (isset($v['required']) && $v['required'] === true) $reqr[] = $k;
                unset($v['required']);
                $pars[$k] = $v;
            }
            if ($method == 'get') {
                $items["/$key"]['get'] = [
                    'tags' => [$key],
                    'operationId' => "get{$name}s",
                    'consumes' => ['application/json'],
                    'produces' => ['application/json'],
                    'parameters' => [
                        [ 'name' => 'id', 'in' => 'query', 'type' => 'string', 'description' => 'CSV list of object IDs to return' ],
                        [ 'name' => 'filter', 'in' => 'query', 'type' => 'string', 'description' => 'Search string filter results by. Filtered columns are: '.$filterCols ],
                        [ 'name' => 'count', 'in' => 'query', 'type' => 'integer', 'description' => 'Maximum number of results to return. Maximum is 100, default is 10' ],
                        [ 'name' => 'sort_descending', 'in' => 'query', 'type' => 'boolean', 'description' => 'Set to true to sort in reverse order' ]
                    ],
                    'responses' => [
                        200 => [
                            'description' => 'Query result',
                            'schema' => [
                                'type' => 'array',
                                'items' => [
                                    '$ref' => "#/definitions/$name"
                                ]
                            ]
                        ]
                    ]
                ];
                $items["/$key/paged"]['get'] = [
                    'tags' => [$key],
                    'operationId' => "get{$name}sPaged",
                    'consumes' => ['application/json'],
                    'produces' => ['application/json'],
                    'parameters' => [
                        [ 'name' => 'id', 'in' => 'query', 'type' => 'string', 'description' => 'CSV list of object IDs to return' ],
                        [ 'name' => 'filter', 'in' => 'query', 'type' => 'string', 'description' => 'Search string filter results by. Filtered columns are: '.$filterCols ],
                        [ 'name' => 'count', 'in' => 'query', 'type' => 'integer', 'description' => 'Maximum number of results to return. Maximum is 100, default is 10' ],
                        [ 'name' => 'page', 'in' => 'query', 'type' => 'integer', 'description' => 'The page of results to return. Works alongside `count`' ],
                        [ 'name' => 'sort_descending', 'in' => 'query', 'type' => 'boolean', 'description' => 'Set to true to sort in reverse order' ]
                    ],
                    'responses' => [
                        200 => [
                            'description' => 'Paged query result',
                            'schema' => [
                                'type' => 'object',
                                'title' => "PagedList[$name]",
                                'properties' => [
                                    'items' => [
                                        'type' => 'array',
                                        'description' => 'The array of results',
                                        'items' => [
                                            '$ref' => "#/definitions/$name"
                                        ]
                                    ],
                                    'total' => [ 'type' => 'integer', 'description' => 'The total number of items' ],
                                    'pages' => [ 'type' => 'integer', 'description' => 'The total number of pages' ],
                                    'page' => [ 'type' => 'integer', 'description' => 'The current page number']
                                ]
                            ]
                        ]
                    ]
                ];
                foreach ($pars as $k => $v) {
                    $items["/$key"]['get']['parameters'][] = array_merge(['name' => $k, 'in' => 'query'], $v);
                    $items["/$key/paged"]['get']['parameters'][] = array_merge(['name' => $k, 'in' => 'query'], $v);
                }
                if (isset($desc['allowed_sort_columns']) && count($desc['allowed_sort_columns']) > 0) {
                    $exp = [
                        'name' => 'sort_by',
                        'in' => 'query',
                        'type' => 'string',
                        'enum' => $desc['allowed_sort_columns'],
                        'description' => 'The column to sort the results by'
                    ];
                    $items["/$key"]['get']['parameters'][] = $exp;
                    $items["/$key/paged"]['get']['parameters'][] = $exp;
                }
                if (count($desc['expand']) > 0) {
                    $exp = [
                        'name' => 'expand',
                        'in' => 'query',
                        'type' => 'string',
                        'description' => 'CSV list of relations to expand. Expandable relationships are: '.implode(', ', $desc['expand'])
                    ];
                    $items["/$key"]['get']['parameters'][] = $exp;
                    $items["/$key/paged"]['get']['parameters'][] = $exp;
                }
                if ($auth) {
                    $items["/$key"]['get']['x-requires-permission'] = $auth;
                    $items["/$key/paged"]['get']['x-requires-permission'] = $auth;
                }
            } else {

                $schm = [
                    'type' => 'object',
                    'properties' => $pars
                ];
                if (count($reqr) > 0) $schm['required'] = $reqr;

                $op = $method;
                if ($method == 'put') $op = 'edit';
                else if ($method == 'post') $op = 'create';

                $items["/$key"][$method] = [
                    'tags' => [$key],
                    'operationId' => "{$op}{$name}s",
                    'consumes' => ['application/json'],
                    'produces' => ['application/json'],
                    'parameters' => [
                        [
                            'name' => 'body',
                            'in' => 'body',
                            'description' => 'Posted data',
                            'schema' => $schm
                        ]
                    ],
                    'responses' => [
                        200 => [
                            'description' => 'Operation successful',
                            'schema' => [
                                '$ref' => "#/definitions/$name"
                            ]
                        ],
                        404 => [
                            'description' => 'Resource not found or insufficient permissions',
                        ],
                        422 => [
                            'description' => 'Validation failed'
                        ]
                    ]
                ];
            }
        }
        if (isset($desc['additional_methods'])) {
            foreach ($desc['additional_methods'] as $n => $add) {
                $params = isset($add['parameters']) ? $add['parameters'] : [];
                $pars = [];
                $reqr = [];
                foreach ($params as $k => $v) {
                    if (isset($v['required']) && $v['required'] === true) $reqr[] = $k;
                    unset($v['required']);
                    $pars[$k] = $v;
                }
                $schm = [
                    'type' => 'object',
                    'properties' => $pars
                ];
                if (count($reqr) > 0) $schm['required'] = $reqr;
                $items["/$key/$n"][$add['method']] = [
                    'tags' => [$key],
                    'operationId' => $add['operationId'],
                    'consumes' => ['application/json'],
                    'produces' => ['application/json'],
                    'parameters' => [
                        [
                            'name' => 'body',
                            'in' => 'body',
                            'description' => 'Posted data',
                            'schema' => $schm
                        ]
                    ],
                    'responses' => [
                        200 => $add['response'],
                        404 => [
                            'description' => 'Resource not found or insufficient permissions',
                        ],
                        422 => [
                            'description' => 'Validation failed'
                        ]
                    ]
                ];
            }
        }

        if (count($items) == 1 && count($items["/$key"]) == 0)
        {
            $items = [];
        }

        return $items;
    }

    public function getIndex()
    {
        $api_desc = [];
        $tags = [];
        foreach ($this->descriptors as $key => $desc) {
            $items = $this->getPathItems($key, $desc);
            foreach ($items as $path => $item) $api_desc[$path] = $item;
            $tags[] = ['name' => $key, 'description' => $desc['description']];
        }
        $swagger = [
            'swagger' => '2.0',
            'info' => [
                'title' => 'THWL JSON API',
                'description' => 'An unnecessarily feature-complete JSON API for TWHL.',
                'termsOfService' => asset('/wiki/page/TWHL:_Site_Rules'),
                'contact' => [
                    'name' => 'TWHL Staff',
                    'url' => asset('/forum/view/meta')
                ],
                'license' => [
                    'name' => 'CC BY-NC-ND',
                    'url' => 'https://creativecommons.org/licenses/by-nc-nd/4.0/'
                ],
                'version' => 'latest'
            ],
            'paths' => $api_desc,
            'host' => preg_replace('%https?://([^/]*)/.*%', '\1', asset('/')),
            'basePath' => '/api',
            'definitions' => $this->getDefinitions(),
            'securityDefinitions' => [
                'api_key' => [
                    'type' => 'apiKey',
                    'name' => 'Authorization',
                    'in' => 'header'
                ]
            ],
            'tags' => $tags,
            'externalDocs' => [
                'url' => asset('/'),
                'description' => 'TWHL main website'
            ]
        ];
        return response()->json($swagger);
    }

    public function missingMethod($parameters = [])
    {
        $request = Request::instance();
        if (count($parameters) > 0 && isset($this->descriptors[$parameters[0]]))
        {
            $desc = $this->descriptors[$parameters[0]];

            $key = str_replace('-', '_', strtolower(implode('_', $parameters)));
            $method = strtolower($request->getMethod());
            $operation = "{$method}_{$key}";

            if ($method != 'get' && !permission(true) && (!isset($desc['allow_unauthenticated']) || $desc['allow_unauthenticated'] !== true)) {
                return response()->json([
                    'message' => 'Unauthorised'
                ])->setStatusCode(401);
            }
            else if (array_search($method, $desc['methods']) === false) {
                // Also Failure
            }
            else if (method_exists($this, $operation)) {
                try {
                    $result = $this->$operation();
                    return response()->json($result);
                } catch (ValidationException $ex) {
                    return $ex->getResponse();
                } catch (\Exception $ex) {
                    return response()->json([
                        'message' => 'Object not found.'
                    ])->setStatusCode(404);
                }
            }
            else if ($method == 'get') {
                $q = call_user_func($desc['object'] . '::query');

                $exp = explode(',', Input::get('expand'));
                foreach ($exp as $e) {
                    if (array_search($e, $desc['expand']) !== false) $q->with($e);
                }

                if (isset($desc['parameters']['get'])) {
                    foreach ($desc['parameters']['get'] as $name => $par) {
                        $req = Input::get($name);
                        if ($req !== null) {
                            $q->where($name, '=', $req);
                        }
                    }
                }

                $sort = $desc['sort_column'];
                $s = Input::get('sort_by');
                if ($s && isset($desc['allowed_sort_columns']) && array_search($s, $desc['allowed_sort_columns']) !== false) {
                    $sort = [$s, $desc['sort_column']];
                }

                $sort_desc = isset($desc['sort_descending']) ? $desc['sort_descending'] : false;
                if (Input::get('sort_descending') === 'true') $sort_desc = true;
                else if (Input::get('sort_descending') === 'false') $sort_desc = false;

                $filtered = $this->filter($q, $desc['filter_columns'], $sort, $sort_desc);
                $array = $this->toArray($filtered, count($parameters) < 2 || $parameters[1] != 'paged');
                return response()->json($array);
            }
        }
        return response()->json([
            'message' => 'Method not found.'
        ])->setStatusCode(404);
    }

    private function filter($query, $filter_cols, $sort_cols = [], $sort_desc = false) {

        $ids = Input::get('id');
        if ($ids) {
            $ids = array_filter(array_map(function($x) { return intval($x); }, explode(',', $ids)), function($x) { return $x > 0; });
            if ($ids) $query = $query-> whereIn('id', $ids);
        }

        if (!is_array($filter_cols)) $filter_cols = [$filter_cols];
        if ($sort_cols && !is_array($sort_cols)) $sort_cols = [$sort_cols];
        if (!$sort_cols || !is_array($sort_cols) || count($sort_cols) == 0) $sort_cols = $filter_cols;

        foreach ($sort_cols as $v) {
            $query = $query->orderBy($v, $sort_desc ? 'desc' : 'asc');
        }

        $filter = Input::get('filter');
        if (!$filter || count($filter_cols) == 0) return $query;
        $filter .= '%';
        $args = [];
        $sql = '1 != 1';
        for ($i = 0; $i < count($filter_cols); $i++) {
            $col = $filter_cols[$i];
            $sql .= " or $col like ?";
            $args[] = $filter;
        }
        return $query->whereRaw("($sql)", $args);
    }

    private function toArray($query, $force_plain = false) {

        $page = Input::get('page');
        if (!$page) $page = 1;

        $count = intval(Input::get('count'));
        if (!$count || $count < 1 || $count > 100) $count = 10;

        $plain = $force_plain;

        $total = $query->getQuery()->getCountForPagination();

        $pages = ceil($total / $count);
        if ($page == 'last' || $page > $pages) $page = $pages;
        else if ($page < 1) $page = 1;

        $items = $query->skip(($page - 1) * $count)->take($count)->get();

        if ($plain) return $items->values()->toArray();
        return [
            'items' => $items->values()->toArray(),
            'total' => $total,
            'pages' => $pages,
            'page' => $page
        ];
    }

    // Post/put

    private function post_posts()
    {
        if (!permission('ForumCreate')) throw new \Exception();

        $id = intval(Request::input('thread_id'));
        $thread = ForumThread::findOrFail($id);
        if (!$thread->isPostable()) throw new \Exception();

        $this->validate(Request::instance(), [
            'content_text' => 'required|max:10000'
        ]);
        $post = ForumPost::Create([
            'thread_id' => Request::input('thread_id'),
            'forum_id' => $thread->forum_id,
            'user_id' => Auth::user()->id,
            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text')),
        ]);
        return $post;
    }

    private function put_posts() {
        if (!permission('ForumCreate')) throw new \Exception();

        $id = intval(Request::input('id'));
        $post = ForumPost::findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        if (!$post->isEditable($thread)) throw new \Exception;

        $this->validate(Request::instance(), [
            'content_text' => 'required|max:10000'
        ]);
        $post->update([
            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text')),
        ]);
        return $post;
    }

    private function post_posts_format() {
        $field = Input::get('field') ?: 'text';
        $text = Input::input($field) ?: '';
        return app('bbcode')->Parse($text);
    }

    private function post_threads() {
        if (!permission('ForumCreate')) throw new \Exception();

        $id = intval(Request::input('forum_id'));
        $forum = Forum::where('id', '=', $id)->firstOrFail();
        $this->validate(Request::instance(), [
            'title' => 'required|max:200',
            'content_text' => 'required|max:10000'
        ]);
        $thread = ForumThread::Create([
            'forum_id' => $id,
            'user_id' => Auth::user()->id,
            'title' => Request::input('title'),
            'is_open' => true
        ]);
        $post = ForumPost::Create([
            'thread_id' => $thread->id,
            'forum_id' => $id,
            'user_id' => Auth::user()->id,
            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text')),
        ]);
        return $thread;
    }

    private function post_comments()
    {
        $comment_config = CommentController::$comment_config;
        $type = Request::input('article_type');
        $id = intval(Request::input('article_id'));

        if (!array_key_exists($type, $comment_config)) throw new \Exception();
        $config = $comment_config[$type];

        if (!permission($config['auth_create'])) throw new \Exception();

        $this->validate(Request::instance(), [
            'content_text' => 'required|max:10000'
        ]);

        $article = call_user_func($config['model'] . '::findOrFail', $id);
        if (!permission('Admin') && $article->commentsIsLocked()) throw new \Exception();

        $comment = Comment::Create([
            'article_type' => $type,
            'article_id' => $id,
            'user_id' => Auth::user()->id,
            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text')),
        ]);
        if (array_key_exists('meta', $config) && is_array($config['meta'])) {
            $metas = [];
            foreach ($config['meta'] as $key => $meta) {
                if (!$article->commentsCanAddMeta($key)) continue;
                $val = strval(Request::input($meta['key']));
                if ($val && preg_match($meta['valid'], $val)) {
                    $metas[] = new CommentMeta([ 'key' => $key, 'value' => $val]);
                    if (isset($meta['one_per_user']) && $meta['one_per_user']) {
                        DB::statement(
                            'DELETE m FROM comment_metas AS m
                            LEFT JOIN comments AS c ON c.id = m.comment_id
                            WHERE c.article_type = ? AND c.article_id = ? AND c.user_id = ? AND m.key = ?',
                            [$type, $id, $comment->user_id, $key]);
                    }
                }
            }
            $comment->comment_metas()->saveMany($metas);
        }
        DB::statement('CALL update_comment_statistics(?, ?, ?);', [$type, $id, $comment->user_id]);
        if (method_exists($article, 'onCommentCreated')) {
            $article->onCommentCreated($comment);
        }
        return $comment;
    }

    private function put_comments() {
        $comment_config = CommentController::$comment_config;

        $comment_id = intval(Request::input('id'));
        $comment = Comment::findOrFail($comment_id);
        if (!$comment->isEditable()) throw new \Exception();

        $type = $comment->article_type;
        $id = $comment->article_id;

        if (!array_key_exists($type, $comment_config)) throw new \Exception();
        $config = $comment_config[$type];

        if (!permission($config['auth_create'])) throw new \Exception();

        $this->validate(Request::instance(), [
            'content_text' => 'required|max:10000'
        ]);

        $article = call_user_func($config['model'] . '::findOrFail', $id);
        $comment->update([
            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text')),
        ]);

        DB::statement('DELETE FROM comment_metas WHERE comment_id = ?', [$comment->id]);
        if (array_key_exists('meta', $config) && is_array($config['meta'])) {
            $metas = [];
            foreach ($config['meta'] as $key => $meta) {
                if (!$article->commentsCanAddMeta($key)) continue;
                $val = strval(Request::input($meta['key']));
                if ($val && preg_match($meta['valid'], $val)) {
                    $metas[] = new CommentMeta([ 'key' => $key, 'value' => $val]);
                    if (isset($meta['one_per_user']) && $meta['one_per_user']) {
                        DB::statement(
                            'DELETE m FROM comment_metas AS m
                            LEFT JOIN comments AS c ON c.id = m.comment_id
                            WHERE c.article_type = ? AND c.article_id = ? AND c.user_id = ? AND m.key = ?',
                            [$type, $id, $comment->user_id, $key]);
                    }
                }
            }
            $comment->comment_metas()->saveMany($metas);
        }
        DB::statement('CALL update_comment_statistics(?, ?, ?);', [$type, $id, $comment->user_id]);
        return $comment;
    }

    private function get_shouts_from() {
        $last = intval(Input::get('timestamp'));
        $car = Carbon::createFromTimestamp($last - 10);
        return Shout::with(['user'])
            ->where('updated_at', '>=', $car)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->reverse()
            ->values();
   	}

    private function post_shouts() {
        $this->validate(Request::instance(), [
            'text' => 'required|max:250'
        ]);
        return Shout::Create([
            'user_id' => Auth::user()->id,
            'content' => Request::input('text')
        ]);
    }

    private function put_shouts() {
        if (!permission('ForumAdmin')) throw new \Exception();

        $this->validate(Request::instance(), [
            'id' => 'required|numeric',
            'text' => 'required|max:250'
        ]);
        $shout = Shout::findOrFail(Request::input('id'));
        $shout->update([
            'content' => Request::input('text')
        ]);
        return $shout;
    }

    private function delete_shouts() {
        if (!permission('ForumAdmin')) throw new \Exception();

        $shout = Shout::findOrFail(Request::input('id'));
        $shout->delete();
        return ['success' => true];
    }

    private function post_api_key() {

        $this->validate(Request::instance(), [
            'app' => 'required|max:255',
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = Auth::getProvider()->retrieveByCredentials([
            'name' => Request::input('username'),
            'password' => Request::input('password')
        ]);
        if (!$user) throw new ModelNotFoundException();

        // Stop API key spam and see if this app already has a key

        $key = ApiKey::whereUserId($user->id)->whereApp(Request::input('app'))->first();
        if ($key) return $key;

        $key = ApiKey::create([
            'user_id' => $user->id,
            'key' => ApiKey::GenerateKey($user->id),
            'app' => Request::input('app'),
            'ip' => Request::ip(),
        ]);

        return $key;
    }
}
