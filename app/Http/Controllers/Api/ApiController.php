<?php

namespace App\Http\Controllers\Api;
 
use App\Events\CommentCreated;
use App\Helpers\Image;
use App\Http\Controllers\Comments\CommentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Wiki\WikiController;
use App\Models\Accounts\ApiKey;
use App\Models\Accounts\Permission;
use App\Models\Accounts\User;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentDetail;
use App\Models\Comments\CommentMeta;
use App\Models\Competitions\Competition;
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
use App\Models\Vault\Motm;
use App\Models\Vault\VaultCategory;
use App\Models\Vault\VaultInclude;
use App\Models\Vault\VaultItem;
use App\Models\Vault\VaultItemReview;
use App\Models\Vault\VaultScreenshot;
use App\Models\Vault\VaultType;
use App\Models\Wiki\WikiObject;
use App\Models\Wiki\WikiPageInformation;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionBook;
use App\Models\Wiki\WikiRevisionCredit;
use App\Models\Wiki\WikiRevisionMeta;
use App\Models\Wiki\WikiType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller {

    private array $descriptors = [
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
            'sort_column' => 'created_at',
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
            'sort_column' => ['is_sticky','updated_at'],
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
            'object' => CommentDetail::class,
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
            'methods' => ['get', 'post', 'put'],
            'auth' => [],
            'parameters' => [
                'get' => [
                    'is_active' => [ 'type' => 'integer', 'enum' => [0,1], 'description' => 'Allows to filter for only active or inactive revisions' ],
                    'object_id' => [ 'type' => 'integer', 'description' => 'The ID of the parent wiki object' ],
                    'user_id' => [ 'type' => 'integer', 'description' => 'The ID of the user' ],
                ],
                'post' => [
                    'title' => [ 'required' => true, 'type' => 'string', 'description' => 'The title of the page to create' ],
                    'content_text' => [ 'required' => true, 'type' => 'string', 'description' => 'The text of the page to create']
                ],
                'put' => [
                    'id' => [ 'required' => true, 'type' => 'integer', 'description' => 'The id of the revision to edit' ],
                    'title' => [ 'required' => true, 'type' => 'string', 'description' => 'The title of the page to edit' ],
                    'content_text' => [ 'required' => true, 'type' => 'string', 'description' => 'The new text of the page'],
                    'message' => [ 'required' => false, 'type' => 'string', 'description' => 'The change message']
                ]
            ],
            'object' => WikiRevision::class,
            'filter_columns' => ['title', 'content_text'],
            'sort_column' => 'title',
            'allowed_sort_columns' => ['title','created_at'],
            'default_filters' => [],
            'additional_methods' => [
                'upload' => [
                    'method' => 'post',
                    'operationId' => 'uploadPost',
                    'parameters' => [
                        'title' => [ 'required' => true, 'type' => 'string', 'description' => 'The title of the page to create' ],
                        'file' => [ 'required' => true, 'type' => 'string', 'description' => 'The file to upload' ],
                        'content_text' => [ 'required' => true, 'type' => 'string', 'description' => 'The text of the page to create']
                    ],
                    'response' => [
                        'description' => 'Revision page',
                        'schema' => [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/WikiRevision'
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'wiki-objects' => [
            'description' => 'Wiki Objects',
            'methods' => ['post'],
            'object' => WikiObject::class,
            'filter_columns' => [],
            'additional_methods' => [
                'page-information' => [
                    'method' => 'post',
                    'operationId' => 'pageInformationPost',
                    'parameters' => [
                        'pages' => [
                            'required' => true,
                            'type' => 'array',
                            'items' => [ 'type' => 'string' ],
                            'description' => 'The list of page slugs to check'
                        ],
                        'embeds' => [
                            'required' => true,
                            'type' => 'array',
                            'items' => [ 'type' => 'string' ],
                            'description' => 'The list of embed slugs to check'
                        ]
                    ],
                    'response' => [
                        'description' => 'Page information',
                        'schema' => [
                            'type' => 'array',
                            'items'=> [ '$ref' => '#/components/schemas/WikiPageInformation' ],
                        ]
                    ]
                ]
            ],
            'allow_unauthenticated' => true
        ],
        'wiki-page-information' => [
            'description' => 'Wiki Page Information',
            'object' => WikiPageInformation::class,
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
        'wiki-revision-book' => [
            'description' => 'Wiki Revision Books',
            'object' => WikiRevisionBook::class,
        ],
        'wiki-revision-credit' => [
            'description' => 'Wiki Revision Credits',
            'object' => WikiRevisionCredit::class,
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
            'default_filters' => [],
            'object_names' => ['VaultCategory','VaultCategories']
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
            'expand' => ['vault_screenshots', 'user', 'engine', 'game', 'license', 'vault_category', 'vault_type', 'vault_includes', 'vault_item_reviews', 'motms'],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [
                'get' => [
                    'user_id' => [ 'type' => 'integer', 'description' => 'The ID of the user' ]
                ]
            ],
            'object' => VaultItem::class,
            'filter_columns' => [ 'name' ],
            'sort_column' => 'created_at',
            'allowed_sort_columns' => ['updated_at'],
            'default_filters' => []
        ],
        'vault-item-review' => [
            'description' => 'Vault Item Reviews',
            'object' => VaultItemReview::class,
        ],
        'motm' => [
            'description' => 'Map of the Month',
            'object' => Motm::class,
        ],
        'competitions' => [
            'description' => 'Competitions',
            'expand' => ['status', 'type', 'judge_type'],
            'methods' => ['get'],
            'auth' => [],
            'parameters' => [
                'get' => [
                    'status_id' => [ 'type' => 'integer', 'description' => 'The status ID of the competition']
                ]
            ],
            'object' => Competition::class,
            'filter_columns' => ['name'],
            'sort_column' => 'created_at',
            'allowed_sort_columns' => ['updated_at', 'id'],
            'default_filters' => []
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
            'default_filters' => [],
            'object_names' => ['CompetitionStatus','CompetitionStatuses']
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
                        'timestamp' => [ 'required' => false, 'type' => 'integer', 'description' => 'The timestamp to retrieve shouts posted after', 'format' => 'unixtime' ]
                    ],
                    'response' => [
                        'description' => 'Recent shouts',
                        'schema' => [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/Shout'
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'image-upload' => [
            'description' => 'Image Upload',
            'expand' => [],
            'methods' => ['post'],
            'auth' => [],
            'parameters' => [
                'post' => [
                    'image' => [ 'required' => true, 'type' => 'string', 'format' => 'binary', 'description' => 'The image data. Maximum size: 2MB' ]
                ]
            ],
            'object' => \stdClass::class,
            'object_names' => ['Image', 'Images'],
            'filter_columns' => [],
            'sort_column' => 'id',
            'allowed_sort_columns' => [],
            'default_filters' => []
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

    const array RATE_LIMIT_HEADERS = [
        'X-RateLimit-Limit' => [
            '$ref' => '#/components/headers/RateLimitLimit'
        ],
        'X-RateLimit-Remaining' => [
            '$ref' => '#/components/headers/RateLimitRemaining'
        ],
    ];

    private function getRelatedClass($obj, $prop): ?array
    {
        $rel = null;
        $o = $obj;
        foreach (explode('.', $prop) as $name) {
            if (!method_exists($o, $name)) return null;
            $rel = $o->$name();
            if (!$rel instanceof Relation) return null;
            $o = $rel->getRelated();
        }

        if ($rel && $o) {
            $rel_expl = explode('\\', get_class($o));
            $rel_name = $rel_expl[count($rel_expl) - 1];
            $type = "#/components/schemas/$rel_name";
            if ($rel instanceof HasMany) {
                $type = 'array';
            }
            return [
                'ref' => "#/components/schemas/$rel_name",
                'type' => $type
            ];
        }

        return null;
    }

    private function objectNamesFromDescriptor($descriptor): array {
        if (isset($descriptor['object_names'])) {
            return $descriptor['object_names'];
        }

        $expl = explode('\\', $descriptor['object']);
        $single = $expl[count($expl) - 1];
        $plural = "{$single}s";
        return [$single, $plural];
    }

    private function objectSchemasFromDescriptors(): array
    {
        $objectSchemas = [];

        foreach ($this->descriptors as $key => $desc) {
            $obj = new $desc['object'];
            [$name] = $this->objectNamesFromDescriptor($desc);
            $props = [];
            foreach (($obj->visible ?? []) as $prop) {
                $props[$prop] = [];

                $type = 'string';
                $type_key = 'type';
                if ($prop == 'id' || $prop == 'orderindex' || $prop == 'order_index' || str_ends_with($prop, '_id')) {
                    $type = 'integer';
                }
                else if (
                    $prop == 'created_at' ||
                    $prop == 'updated_at' ||
                    $prop == 'deleted_at' ||
                    in_array($prop, $obj->dates ?? []) ||
                    ($obj instanceof Model && $obj->hasCast($prop, 'datetime'))
                ) {
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
            $objectSchemas[$name] = [
                'type' => 'object',
                'properties' => count($props) == 0 ? new \stdClass() : $props
            ];
        }

        return $objectSchemas;
    }

    private function objectShemaFromParameterDescription(array $parameters): array
    {
        $required = [];

        foreach ($parameters as $key => $def) {
            if ($def['required']) {
                $required[] = $key;
            }
            unset($parameters[$key]['required']);
        }

        $result = [
            'properties' => count($parameters) === 0 ? new \stdClass() : $parameters,
            'type' => 'object'
        ];

        if (!empty($required)) {
            $result['required'] = $required;
        }

        return $result;
    }


    private function responsesFromDescriptors(): array
    {
        $responses = [];

        foreach ($this->descriptors as $key => $desc) {
            [$name, $pluralName] = $this->objectNamesFromDescriptor($desc);

            foreach ($desc['methods'] ?? [] as $method) {
                if ($method == 'get') {
                    $responseName = $this->responseName($method, $pluralName);

                    $responses[$responseName] = [
                        'description' => 'Query result',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        '$ref' => "#/components/schemas/$name"
                                    ]
                                ],
                            ]
                        ],
                        'headers' => self::RATE_LIMIT_HEADERS
                    ];

                    $pagedResponseName = $this->responseName($method, $pluralName, true);

                    $responses[$pagedResponseName] = [
                        'description' => 'Paged query result',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'properties' => [
                                        'items' => [
                                            'type' => 'array',
                                            'items' => [
                                                '$ref' => "#/components/schemas/$name",
                                            ]
                                        ],
                                        'page' => [
                                            'description' => 'The current page number',
                                            'format' => 'uint32',
                                            'type' => 'integer',
                                        ],
                                        'pages' => [
                                            'description' => 'The total number of pages',
                                            'format' => 'uint32',
                                            'type' => 'integer'
                                        ],
                                        'total' => [
                                            'description' => 'The total number of items',
                                            'format' => 'uint32',
                                            'type' => 'integer'
                                        ]
                                    ],
                                    'required' => ['items', 'page', 'pages', 'total'],
                                    'type' => 'object',
                                ],
                            ]
                        ],
                        'headers' => self::RATE_LIMIT_HEADERS
                    ];
                } else {
                    $responseName = $this->responseName($method, $name);

                    $responses[$responseName] = [
                        'description' => 'Operation successful',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => "#/components/schemas/$name"
                                ]
                            ]
                        ],
                        'headers' => self::RATE_LIMIT_HEADERS
                    ];
                }
            }
            foreach ($desc['additional_methods'] ?? [] as $op => $add) {
                $responseName = $this->responseName($add['operationId'], $name);

                $responses[$responseName] = [
                    'description' => $add['response']['description'],
                    'content' => [
                        'application/json' => [
                            'schema' => $add['response']['schema']
                        ]
                    ],
                    'headers' => self::RATE_LIMIT_HEADERS
                ];
            }
        }
        return $responses;
    }

    private function getPathItems($key, $desc): array
    {
        $items = [];
        $items["/$key"] = [];

        [$name, $pluralName] = $this->objectNamesFromDescriptor($desc);
        $filterCols = implode(', ', $desc['filter_columns'] ?? []);

        foreach ($desc['methods'] ?? [] as $method) {
            $auth = $desc['auth'][$method] ?? null;
            $security = [ $auth ? new \stdClass() : [ 'httpBearerToken' => [] ] ];
            $params = $desc['parameters'][$method] ?? [];
            if ($method == 'get') {
                $allParameters = [
                    'count' => [
                        'default' => 10,
                        'description' => 'Maximum number of results to return. Maximum is 100, default is 10',
                        'format' => 'uint16',
                        'minimum' => 1,
                        'maximum' => 100,
                        'type' => 'integer'
                    ],
                    'filter' => [
                        'description' => 'Search string to filter results by. Filtered columns are: ' . $filterCols,
                        'type' => 'string'
                    ],
                    'id' => [
                        'description' => 'IDs of objects to return',
                        'explode' => false,
                        'items' => [
                            'format' => 'uint32',
                            'type' => 'integer'
                        ],
                        'type' => 'array',
                        'uniqueItems' => true
                    ],
                    'sort_descending' => [
                        'default' => false,
                        'description' => 'Set to true to sort in reverse order',
                        'type' => 'boolean'
                    ],
                    ...$params
                ];
                $responseName = $this->responseName($method, $pluralName);
                $items["/$key"]['get'] = [
                    'tags' => [$key],
                    'operationId' => "get{$pluralName}",
                    'parameters' => $this->makeParameterSet($allParameters),
                    'responses' => [
                        '200' => [
                            '$ref' => "#/components/responses/{$responseName}"
                        ]
                    ],
                    'security' => $security
                ];
                $pagedParameters = [
                    'page' => [
                        'description' => 'The page of results to return. Works alongside `count`',
                        'format' => 'uint32',
                        'type' => 'integer'
                    ],
                    ...$allParameters
                ];
                $pagedResponseName = $this->responseName($method, $pluralName, true);
                $items["/$key/paged"]['get'] = [
                    'tags' => [$key],
                    'operationId' => "get{$pluralName}Paged",
                    'parameters' => $this->makeParameterSet($pagedParameters),
                    'responses' => [
                        '200' => [
                            '$ref' => "#/components/responses/{$pagedResponseName}"
                        ]
                    ],
                    'security' => $security
                ];
                if (!empty($desc['allowed_sort_columns'])) {
                    $exp = [
                        'name' => 'sort_by',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'string',
                            'enum' => $desc['allowed_sort_columns']
                        ],
                        'description' => 'The column to sort the results by'
                    ];
                    $items["/$key"]['get']['parameters'][] = $exp;
                    $items["/$key/paged"]['get']['parameters'][] = $exp;
                }
                if (!empty($desc['expand'])) {
                    $exp = [
                        'description' => 'Relations to expand.',
                        'explode' => false,
                        'in' => 'query',
                        'name' => 'expand',
                        'schema' => [
                            'items' => [
                                'enum' => $desc['expand'],
                                'type' => 'string',
                            ],
                            'type' => 'array',
                            'uniqueItems' => true
                        ],
                    ];
                    $items["/$key"]['get']['parameters'][] = $exp;
                    $items["/$key/paged"]['get']['parameters'][] = $exp;
                }
            } else {
                $op = $method;
                if ($method == 'put') $op = 'edit';
                else if ($method == 'post') $op = 'create';

                $requestBodyName = $this->requestBodyName($method, $name);
                $responseName = $this->responseName($method, $name);
                $items["/$key"][$method] = [
                    'tags' => [$key],
                    'operationId' => "{$op}{$name}",
                    'parameters' => $this->makeParameterSet([]),
                    'requestBody' => [
                        '$ref' => "#/components/requestBodies/{$requestBodyName}",
                    ],
                    'responses' => [
                        200 => [
                            '$ref' => "#/components/responses/{$responseName}"
                        ],
                        404 => [
                            '$ref' => '#/components/responses/NotFoundErrorResult'
                        ],
                        422 => [
                            '$ref' => '#/components/responses/ValidationFailedErrorResult'
                        ]
                    ],
                    'security' => $security
                ];
            }
        }

        foreach ($desc['additional_methods'] ?? [] as $op => $add) {
            $params = $add['parameters'] ?? [];

            $responseName = $this->responseName($add['operationId'], $name);
            $parameterSet = $this->makeParameterSet($add['method'] === 'get' ? $params : []);
            $items["/$key/$op"][$add['method']] = [
                'tags' => [$key],
                'operationId' => $add['operationId'],
                'parameters' => $parameterSet,
                'responses' => [
                    200 => [
                        '$ref' => "#/components/responses/{$responseName}"
                    ],
                    404 => [
                        '$ref' => '#/components/responses/NotFoundErrorResult'
                    ],
                    422 => [
                        '$ref' => '#/components/responses/ValidationFailedErrorResult'
                    ]
                ]
            ];

            if ($add['method'] !== 'get') {
                $requestBodyName = $this->requestBodyName($add['operationId'], $name);
                $items["/$key/$op"][$add['method']]['requestBody'] = [
                    '$ref' => "#/components/requestBodies/{$requestBodyName}"
                ];
            }

        }

        if (count($items) == 1 && count($items["/$key"]) == 0)
        {
            $items = [];
        }

        return $items;
    }

    private function operationRelatedObjectNamePrefix($operationName, $paged, $objectName): string
    {
        $op = $operationName;
        if ($operationName === 'put') {
            $op = 'edit';
        } else if ($operationName === 'post') {
            $op = 'create';
        } else if ($operationName !== 'delete' && $operationName !== 'get') {
            $objectName = '';
        }
        $op = str_replace("-", "", ucwords($op, "-"));
        $result = "{$op}{$objectName}";
        if ($paged && $objectName) {
            $result .= 'Paged';
        }
        return $result;
    }

    private function requestBodyName($operationName, $objectName): string
    {
        $prefix = $this->operationRelatedObjectNamePrefix($operationName, false, $objectName);
        return "{$prefix}Body";
    }

    private function responseName($operationName, $objectName, $paged = false): string
    {
        $prefix = $this->operationRelatedObjectNamePrefix($operationName, $paged, $objectName);
        return "{$prefix}Result";
    }

    private function makeRequestBodyObject($params): array
    {
        $bodyRequired = array_any($params, fn ($p) => $p['required'] ?? false);

        return [
            'content' => [
                'application/json' => [
                    'schema' => $this->objectShemaFromParameterDescription($params)
                ]
            ],
            'description' => "Posted data",
            'required' => $bodyRequired
        ];
    }

    private function getRequestBodies(): array
    {
        $requestBodies = [];
        foreach ($this->descriptors as $key => $desc) {
            [$name] = $this->objectNamesFromDescriptor($desc);

            foreach (($desc['methods'] ?? []) as $method) {
                if ($method === 'get') {
                    continue;
                }

                $params = $desc['parameters'][$method] ?? [];
                $requestBodyName = $this->requestBodyName($method, $name);
                $requestBodies[$requestBodyName] = $this->makeRequestBodyObject($params);
            }

            foreach (($desc['additional_methods'] ?? []) as $op => $add) {
                if ($add['method'] === 'get') {
                    continue;
                }

                $params = $add['parameters'] ?? [];
                $requestBodyName = $this->requestBodyName($add['operationId'], $name);
                $requestBodies[$requestBodyName] = $this->makeRequestBodyObject($params);
            }
        }

        ksort($requestBodies);
        return $requestBodies;
    }

    private function makeParameterSet($params): array
    {
        $parameterSet = [];
        foreach ($params as $parameterName => $p) {
            $p['in'] = 'query';
            $p['name'] = $parameterName;
            $p['schema'] = [];
            foreach (array_intersect_key($p, array_flip(['default', 'enum', 'format', 'items', 'maximum', 'minimum', 'type', 'uniqueItems'])) as $typePropertyKey => $typePropertyValue) {
                $p['schema'][$typePropertyKey] = $p[$typePropertyKey];
                unset($p[$typePropertyKey]);
            }
            $parameterSet[] = $p;
        }

        // This header stops the API key owner from showing up in the "Active Users" after
        // every API request
        $parameterSet[] = [
            'example' => 'XMLHttpRequest',
            'in' => 'header',
            'name' => 'X-Requested-With',
            'schema' => [
                'enum' => [ 'XMLHttpRequest' ],
                'type' => 'string'
            ]
        ];
        return $parameterSet;
    }

    public function getIndex(): \Illuminate\Http\JsonResponse
    {
        $api_desc = [];
        $tags = [];
        foreach ($this->descriptors as $key => $desc) {
            $items = $this->getPathItems($key, $desc);
            foreach ($items as $path => $item) $api_desc[$path] = $item;
            $tags[] = ['name' => $key, 'description' => $desc['description']];
        }
        $openapi = [
            '$schema'=> 'https://spec.openapis.org/oas/3.0/schema/2024-10-18',
            'components' => [
                'headers' => [
                    'RateLimitLimit' => [
                        'description' => 'The number of allowed requests in a time window',
                        'schema' => [
                            'format' => 'uint16',
                            'type' => 'integer'
                        ]
                    ],
                    'RateLimitRemaining' => [
                        'description' => 'The number of allowed requests in the current time window',
                        'schema' => [
                            'format' => 'uint16',
                            'type' => 'integer'
                        ]
                    ]
                ],
                'requestBodies' => $this->getRequestBodies(),
                'responses' => [
                    ...$this->responsesFromDescriptors(),
                    # 401
                    'UnauthorizedErrorResult' => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorDescription'
                                ]
                            ]
                        ],
                        'description' => 'Unauthorized. You either lack permission, or your API key is invalid, or you did not provide an API key',
                        'headers' => self::RATE_LIMIT_HEADERS
                    ],
                    # 404
                    'NotFoundErrorResult' => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ErrorDescription'
                                ]
                            ]
                        ],
                        'description' => 'Resource not found or insufficient permissions',
                        'headers' => self::RATE_LIMIT_HEADERS
                    ],
                    # 422
                    'ValidationFailedErrorResult' => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                  '$ref' => '#/components/schemas/ErrorDescription'
                                ]
                            ]
                        ],
                        'description' => 'Validation failed',
                        'headers' => self::RATE_LIMIT_HEADERS
                    ],
                    # 429 - Too Many Requests. Any endpoint can return this response
                    'TooManyErrorResult' => [
                        'content' => [
                            'text/html' => new \stdClass()
                        ],
                        'description' => 'Response you get if you send too many requests in a short amount of time',
                        'headers' => [
                            ...self::RATE_LIMIT_HEADERS,
                            'Retry-After' => [
                                'description' => 'Seconds until you will be allowed to send requests again',
                                'schema' => [
                                    'format' => 'uint16',
                                    'type' => 'integer'
                                ]
                            ],
                            'X-RateLimit-Reset' => [
                                'description' => 'The time as a UNIX timestamp when you will be allowed to send requests again',
                                'schema' => [
                                    'format' => 'unixtime',
                                    'type' => 'integer'
                                ]
                            ]
                        ]
                    ]
                ],
                'schemas' => [
                    'ErrorDescription' => [
                        'properties' => [
                            'message' => [
                                'type' => 'string'
                            ]
                        ],
                        'required' => ['message'],
                        'type' => 'object'
                    ],
                    ...$this->objectSchemasFromDescriptors()
                ],
                'securitySchemes' => [
                    'httpBearerToken' => [
                        "scheme" => "bearer",
                        "type" => "http"
                    ]
                ]
            ],
            'externalDocs' => [
                'url' => asset('/'),
                'description' => 'TWHL main website'
            ],
            'info' => [
                'title' => 'THWL JSON API',
                'description' => 'An unnecessarily feature-complete JSON API for TWHL.',
                'termsOfService' => asset('/wiki/page/TWHL:_Terms_of_Service'),
                'contact' => [
                    'name' => 'TWHL Staff',
                    'url' => asset('/forum/view/meta')
                ],
                'license' => [
                    'name' => 'MIT',
                    'url' => 'https://opensource.org/license/MIT'
                ],
                'version' => 'latest'
            ],
            'openapi' => '3.0.4',
            'paths' => $api_desc,
            'security' => [
                new \stdClass(), // Security token is optional
                [
                    'httpBearerToken' => []
                ]
            ],
            'servers' => [
               [
                'url' => asset('/api')
               ]
            ],
            'tags' => $tags
        ];
        return response()->json($openapi, 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function missingMethod($parameters = [])
    {
        if (!is_array($parameters)) {
            $parameters = explode('/', $parameters);
        }
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
                    return response()->json($ex->validator->errors())->setStatusCode(422);
                } catch (\Exception $ex) {
                    return response()->json([
                        'message' => 'Object not found.'
                    ])->setStatusCode(404);
                }
            }
            else if ($method == 'get') {
                $q = call_user_func($desc['object'] . '::query');

                $exp = explode(',', Request::input('expand'));
                foreach ($exp as $e) {
                    if (array_search($e, $desc['expand']) !== false) $q->with($e);
                }

                if (isset($desc['parameters']['get'])) {
                    foreach ($desc['parameters']['get'] as $name => $par) {
                        $req = Request::input($name);
                        if ($req !== null) {
                            $q->where($name, '=', $req);
                        }
                    }
                }

                $sort = $desc['sort_column'];
                if (!is_array($sort)) $sort = [$sort];
                $s = Request::input('sort_by');
                if ($s && isset($desc['allowed_sort_columns']) && array_search($s, $desc['allowed_sort_columns']) !== false) {
                    if (!is_array($s)) $s = [$s];
                    $sort = array_merge($s, $sort);
                }

                $sort_desc = isset($desc['sort_descending']) ? $desc['sort_descending'] : false;
                if (Request::input('sort_descending') === 'true') $sort_desc = true;
                else if (Request::input('sort_descending') === 'false') $sort_desc = false;

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

        $ids = Request::input('id');
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

        $filter = Request::input('filter');
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

        $page = Request::input('page');
        if (!$page) $page = 1;

        $count = intval(Request::input('count'));
        if (!$count || $count < 1 || $count > 100) $count = 10;

        $plain = $force_plain;

        $total = $query->getQuery()->getCountForPagination();

        $pages = ceil($total / $count);
        if ($page == 'last' || $page > $pages) $page = $pages;
        else if ($page < 1) $page = 1;
        else $page = intval($page);

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
            'content_html' => bbcode(Request::input('content_text')),
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
            'content_html' => bbcode(Request::input('content_text')),
        ]);
        return $post;
    }

    private function post_posts_format(): string
    {
        $field = Request::input('field') ?: 'text';
        $text = Request::input($field) ?: '';
        return bbcode($text);
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
            'content_html' => bbcode(Request::input('content_text')),
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
            'content_html' => bbcode(Request::input('content_text')),
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
        event(new CommentCreated($comment));
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
            'content_html' => bbcode(Request::input('content_text')),
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
        $last = intval(Request::input('timestamp'));
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

    private function delete_shouts(): array
    {
        if (!permission('ForumAdmin')) throw new \Exception();

        $shout = Shout::findOrFail(Request::input('id'));
        $shout->delete();
        return ['success' => true];
    }

    private function post_wiki_revisions(): WikiRevision
    {
        if (!permission('Admin')) throw new \Exception();

        Validator::extend('unique_wiki_slug', function($attribute, $value, $parameters) {
            $s = WikiRevision::CreateSlug($value);
            $rev = WikiRevision::where('is_active', '=', 1)->where('slug', '=', $s)->first();
            return $rev == null;
        });
        Validator::extend('valid_categories', function($attribute, $value, $parameters) {
            return !preg_match('/\[cat:[^\r\n\]]*[^a-z0-9- _\'\r\n\]][^\r\n\]]*\]/i', $value);
        });
        Validator::extend('category_name_must_exist', function($attribute, $value, $parameters) {
            if (substr($value, 0, 9) != 'category:') return true;
            $cat_name = WikiRevision::CreateSlug(substr($value, 9));
            $meta = WikiRevisionMeta::where('key', '=', WikiRevisionMeta::CATEGORY)->where('value', '=', $cat_name)->first();
            return $meta !== null;
        });
        Validator::extend('invalid_title', function($attribute, $value, $parameters) {
            if (WikiRevision::titleContainsDisallowedCharacters($value)) return false;
            return substr($value, 0, 7) != 'upload:';
        });
        $this->validate(Request::instance(), [
            'title' => 'required|max:200|unique_wiki_slug|category_name_must_exist|invalid_title',
            'content_text' => 'required|max:65536|valid_categories',
            'message' => 'max:200'
        ], [
            'unique_wiki_slug' => 'The URL of this page is not unique, change the title to create a URL that doesn\'t already exist.',
            'valid_categories' => 'Category names must only contain letters, numbers, and spaces. Example: [cat:Name]',
            'invalid_title' => "A page title cannot start with ':upload' and cannot contain any of these characters: " . WikiRevision::disallowedTitleCharacters(),
            'category_name_must_exist' => 'This category name doesn\'t exist. Apply this category to at least one object before creating the category page.'
        ]);

        $type = WikiType::PAGE;
        if (substr(Request::input('title'), 0, 9) == 'category:') $type = WikiType::CATEGORY;
        $object = WikiObject::Create([ 'type_id' => $type ]);
        $revision = WikiController::createRevision($object);
        return $revision;
    }

    private function put_wiki_revisions(): WikiRevision
    {
        if (!permission('Admin')) throw new \Exception();

        $id = intval(Request::input('id'));
        $rev = WikiRevision::findOrFail($id);
        $obj = WikiObject::findOrFail($rev->object_id);

        if (!$obj->canEdit()) return abort(404);

        Validator::extend('unique_wiki_slug', function($attribute, $value, $parameters) use ($obj) {
            if ($obj->type_id == WikiType::UPLOAD) {
                $value = 'upload:'.$value;
            }
            $s = WikiRevision::CreateSlug($value);
            $rev = WikiRevision::where('is_active', '=', 1)->where('slug', '=', $s)->where('object_id', '!=', $obj->id)->first();
            return $rev == null;
        });
        Validator::extend('must_change', function($attribute, $value, $parameters) use ($rev, $obj) {
            return trim($rev->content_text) != trim(Request::input('content_text'))
                || trim($rev->title) != trim(Request::input('title'))
                || ($obj->type_id == WikiType::UPLOAD && Request::file('file')
                || (permission('WikiAdmin') && $obj->permission_id != Request::input('permission_id')));
        });
        Validator::extend('valid_categories', function($attribute, $value, $parameters) {
            return !preg_match('/\[cat:[^\r\n\]]*[^a-z0-9- _\'\r\n\]][^\r\n\]]*\]/i', $value);
        });
        Validator::extend('invalid_title', function($attribute, $value, $parameters) use ($obj, $rev) {
            if (WikiRevision::titleContainsDisallowedCharacters($value)) return false;
            return ($obj->type_id != WikiType::PAGE) ||
                   (substr($value, 0, 9) != 'category:' && substr($value, 0, 7) != 'upload:');
        });
        Validator::extend('valid_extension', function($attribute, $value, $parameters) {
            return in_array(strtolower($value->getClientOriginalExtension()), $parameters);
        });
        $max_size = 1024*4;
        $allowed_extensions = 'jpeg,jpg,png,gif,mp3,mp4';
        if (permission('Admin')) {
            $max_size = 1024*64;
            $allowed_extensions .= ',zip,rar,exe,msi';
        }
        $rules = [
            'file' => "max:$max_size|valid_extension:$allowed_extensions",
            'content_text' => 'required|max:65536|must_change|valid_categories',
            'message' => 'max:200'
        ];
        if ($obj->type_id == WikiType::PAGE || $obj->type_id == WikiType::UPLOAD) {
            $rules['title'] = 'required|max:200|unique_wiki_slug|invalid_title';
        }
        $this->validate(Request::instance(), $rules, [
            'must_change' => 'At least one field must be changed to apply an edit.',
            'unique_wiki_slug' => 'The URL of this page is not unique, change the title to create a URL that doesn\'t already exist.',
            'valid_categories' => 'Category names must only contain letters, numbers, and spaces. Example: [cat:Name]',
            'invalid_title' => "A page title cannot start with ':category' or ':upload' and cannot contain any of these characters: " . WikiRevision::disallowedTitleCharacters(),
            'valid_extension' => 'Only the following file formats are allowed: jpg, png, gif'
        ]);
        $revision = WikiController::createRevision($obj, $rev);
        return $revision;
    }

    private function post_wiki_revisions_upload(): WikiRevision
    {
        if (!permission('Admin')) throw new \Exception();

        Validator::extend('unique_wiki_slug', function($attribute, $value, $parameters) {
            $s = WikiRevision::CreateSlug('upload:'.$value);
            $rev = WikiRevision::where('is_active', '=', 1)->where('slug', '=', $s)->first();
            return $rev == null;
        });
        Validator::extend('valid_categories', function($attribute, $value, $parameters) {
            return !preg_match('/\[cat:[^\r\n\]]*[^a-z0-9- _\'\r\n\]][^\r\n\]]*\]/i', $value);
        });
        Validator::extend('valid_extension', function($attribute, $value, $parameters) {
            return in_array(strtolower($value->getClientOriginalExtension()), $parameters);
        });

        $max_size = 1024*4;
        $allowed_extensions = 'jpeg,jpg,png,gif,mp3,mp4';
        if (permission('Admin')) {
            $max_size = 1024*64;
            $allowed_extensions .= ',zip,rar,exe,msi';
        }

        $this->validate(Request::instance(), [
            'title' => 'required|max:200|unique_wiki_slug',
            'file' => "required|max:{$max_size}|valid_extension:{$allowed_extensions}",
            'content_text' => 'required|max:65536|valid_categories',
            'message' => 'max:200'
        ], [
            'unique_wiki_slug' => 'The URL of this page is not unique, change the title to create a URL that doesn\'t already exist.',
            'valid_categories' => 'Category names must only contain letters, numbers, and spaces. Example: [cat:Name]',
            'valid_extension' => 'Only the following file formats are allowed: ' . $allowed_extensions
        ]);
        $type = WikiType::UPLOAD;
        $object = WikiObject::Create([ 'type_id' => $type ]);
        $revision = WikiController::createRevision($object);
        return $revision;
    }

    private function post_wiki_objects_page_information(): array
    {
        $pages = \request()->input('pages');
        $embeds = \request()->input('embeds');

        $res = [
            'pages' => [],
            'embeds' => []
        ];

        foreach ($pages as $page) $res['pages'][strtolower($page)] = [ 'exists' => false, 'slug' => $page ];
        $revs = WikiRevision::where('is_active', '=', 1)->whereIn('slug', $pages)->get();
        foreach ($revs as $rev) {
            $res['pages'][strtolower($rev->slug)] = [
                'slug' => $rev->slug,
                'exists' => true,
                'revision' => $rev
            ];
        }

        foreach ($embeds as $embed) $res['embeds'][strtolower($embed)] = [ 'exists' => false, 'slug' => $embed ];
        $revs = WikiRevision::with(['wiki_revision_metas'])->where('is_active', '=', 1)->whereIn('slug', array_map(function ($e) { return "upload:$e"; }, $embeds))->get();
        foreach ($revs as $rev) {
            $upload = $rev->getUpload();
            $s = substr($rev->slug, 7);
            $res['embeds'][strtolower($s)] = [
                'slug' => $s,
                'exists' => !!$rev && !!$upload,
                'revision' => $rev,
                'upload' => $upload,
                'meta' => $rev ? $rev->wiki_revision_metas : null
            ];
        }

        return $res;
    }

    private function post_image_upload(): array
    {
        $allowed_mimetypes = [
            'image/avif',
            'image/gif',
            'image/jpeg',
            'image/png',
            'image/webp'
        ];
        $this->validate(Request::instance(), [
            // Note: When changing the `max:` value you need to also change `maxImageUploadSize`
            // in `wikicode-preview.js` to the same value multiplied by 1024.
            // When changing the `max_width` or the `max_height` you also need to update wikicode-preview.js
            'image' => 'required|max:2048|mimetypes:' . implode(',', $allowed_mimetypes) . '|dimensions:max_width=2000,max_height=2000'
        ], [
            'dimensions' => 'The image cannot have a width or height of more than 2000 pixels',
        ]);

        $image = Request::file('image');
        $name = Str::uuid()->toString();

        // Use 2000 maximum to support 1080p screenshots without resizing
        $thumbs = Image::MakeThumbnails($image->getPathname(),
            [ [ 'width' => 2000, 'height' => 2000, 'force' => true ] ],
            public_path('uploads/images/' . Auth::id()),
            $name, false
        );

        return [
            'url' => asset('uploads/images/' . Auth::id() . '/' . $thumbs[0])
        ];
    }

    private function post_api_key() {

        $this->validate(Request::instance(), [
            'app' => 'required|max:255',
            'username' => 'required',
            'password' => 'required'
        ]);

        $prov = Auth::getProvider();
        $creds = [
            'name' => Request::input('username'),
            'password' => Request::input('password')
        ];
        $user = $prov->retrieveByCredentials($creds);
        if (!$user) throw new ModelNotFoundException();
        if (!Auth::getProvider()->validateCredentials($user, $creds)) throw new ModelNotFoundException();

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
