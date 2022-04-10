<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\DownloadCounter;
use App\Models\Badge;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenApi\Annotations as OA;
use stdClass;

/**
 * Class PublicController.
 *
 * @author annejan@badge.team
 */
class PublicController extends Controller
{
    /**
     * Fields allowed for ordering the main view ;).
     *
     * @var array<string>
     */
    private $orderFields = [
        'id',
        'name',
        'published_at',
    ];

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $badge = null;
        if ($request->has('badge')) {
            /** @var Badge $badge */
            $badge = Badge::where('slug', $request->get('badge'))->firstOrFail();

            return $this->badge($badge, $request);
        }
        /** @var Builder $projects */
        $projects = Project::whereHas(
            'versions',
            function ($query) {
                $query->published();
            }
        );

        return $this->returnProjectView($request, $projects);
    }

    /**
     * @param Badge   $badge
     * @param Request $request
     *
     * @return View
     */
    public function badge(Badge $badge, Request $request)
    {
        $projects = $badge->projects()->whereHas(
            'versions',
            function ($query) {
                $query->published();
            }
        );

        return $this->returnProjectView($request, $projects, $badge->slug);
    }

    /**
     * @param Request               $request
     * @param BelongsToMany|Builder $projects
     * @param string                $badge    = ''
     *
     * @return View
     */
    private function returnProjectView(Request $request, $projects, string $badge = '')
    {
        $category = '';
        if ($request->has('category')) {
            $category = Category::where('slug', $request->get('category'))->firstOrFail();
            $projects = $projects->where('category_id', $category->id);
            $category = $category->slug;
        }

        $orderField = 'id';
        $orderDirection = 'desc';
        if ($request->has('order') && in_array($request->get('order'), $this->orderFields)) {
            $orderField = $request->get('order');
            if ($request->has('direction') && $request->get('direction') === 'asc') {
                $orderDirection = 'asc';
            }
        }

        $projects = $projects->orderBy($orderField, $orderDirection);

        $appends = [];

        if ($badge !== '') {
            $appends['badge'] = $badge;
        }
        if ($category !== '') {
            $appends['category'] = $category;
        }
        if ($badge !== '') {
            $appends['badge'] = $badge;
        }
        if ($orderField !== '') {
            $appends['order'] = $orderField;
            $appends['direction'] = $orderDirection;
        }

        return view('welcome')->with(
            [
                'users'     => User::count(),
                'projects'  => Project::count(),
                'published' => $projects->paginate(50),
                'badge'     => $badge,
                'category'  => $category,
                'order'     => $orderField,
                'direction' => $orderDirection,
                'appends'   => $appends,
            ]
        );
    }

    /**
     * Get the latest released version of a project.
     *
     * @OA\Get(
     *   path="/eggs/get/{project}/json",
     * @OA\Parameter(
     *     name="project",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="game_of_life")
     *   ),
     *   tags={"Egg"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param string $slug
     *
     * @return JsonResponse
     */
    public function projectJson(string $slug): JsonResponse
    {
        /** @var Project|null $project */
        $project = Project::where('slug', $slug)->first();
        if ($project === null) {
            return response()->json(['message' => 'No releases found'], 404, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
        }
        $releases = [];
        foreach ($project->versions()->published()->orderBy('revision', 'desc')->limit(5)->get() as $version) {
            /* @var Version $version */
            $releases[$version->revision] = [['url' => url($version->zip)]];
        }

        $version = $project->versions()->published()->get()->last();

        if (empty($version)) {
            return response()->json(['message' => 'No releases found'], 404);
        }

        $package = new stdClass();
        $package->info = ['version' => (string) $version->revision];
        $package->description = $project->description;
        $package->name = $project->name;
        $package->category = $project->category;
        $package->releases = $releases;
        $package->min_firmware = $project->min_firmware;
        $package->max_firmware = $project->max_firmware;

        event(new DownloadCounter($project));

        return response()->json($package, 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the latest released versions.
     *
     * @OA\Get(
     *   path="/eggs/list/json",
     *   tags={"Egg"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @return JsonResponse
     */
    public function listJson(): JsonResponse
    {
        return response()->json(
            Project::whereHas(
                'versions',
                function ($query) {
                    $query->published();
                }
            )->orderBy('id', 'DESC')->get(),
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Find the latest released versions.
     *
     * @OA\Get(
     *   path="/eggs/search/{words}/json",
     * @OA\Parameter(
     *     name="words",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", example="cool")
     *   ),
     *   tags={"Egg"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param string $search
     *
     * @return JsonResponse
     */
    public function searchJson(string $search): JsonResponse
    {
        $what = '%' . $search . '%';

        return response()->json(
            Project::whereHas(
                'versions',
                function ($query) {
                    $query->published();
                }
            )->where('name', 'like', $what)
            ->orderBy('id', 'DESC')
            ->get(),
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
        // @todo possibly search in README.md
    }

    /**
     * Get the latest released versions in a category.
     *
     * @OA\Get(
     *   path="/eggs/category/{category}/json",
     * @OA\Parameter(
     *     name="category",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="utility")
     *   ),
     *   tags={"Egg"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param Category $category
     *
     * @return JsonResponse
     */
    public function categoryJson(Category $category): JsonResponse
    {
        return response()->json(
            $category->projects()->whereHas(
                'versions',
                function ($query) {
                    $query->published();
                }
            )->orderBy('id', 'DESC')->get(),
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Get a list of the categories.
     *
     * @OA\Get(
     *   path="/eggs/categories/json",
     *   tags={"Egg"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @return JsonResponse
     */
    public function categoriesJson(): JsonResponse
    {
        return response()->json(Category::where('hidden', false)->get(), 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the latest released versions for a badge model.
     *
     * @OA\Get(
     *   path="/basket/{badge}/list/json",
     * @OA\Parameter(ref="#/components/parameters/badge"),
     *   tags={"Basket"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param Badge $badge
     *
     * @return JsonResponse
     */
    public function badgeListJson(Badge $badge): JsonResponse
    {
        return response()->json(
            $badge->projects()->whereHas(
                'versions',
                function ($query) {
                    $query->published();
                }
            )->orderBy('id', 'DESC')->get(),
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Find the latest released versions for a badge model.
     *
     * @OA\Get(
     *   path="/basket/{badge}/search/{words}/json",
     * @OA\Parameter(ref="#/components/parameters/badge"),
     * @OA\Parameter(
     *     name="words",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", example="cool")
     *   ),
     *   tags={"Basket"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param Badge  $badge
     * @param string $search
     *
     * @return JsonResponse
     */
    public function badgeSearchJson(Badge $badge, string $search): JsonResponse
    {
        $what = '%' . $search . '%';

        return response()->json(
            $badge->projects()->whereHas(
                'versions',
                function ($query) {
                    $query->published();
                }
            )->where('name', 'like', $what)
            ->orderBy('id', 'DESC')
            ->get(),
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
        // @todo possibly search in README.md
    }

    /**
     * Get a list of the categories for a badge.
     *
     * @OA\Get(
     *   path="/basket/{badge}/categories/json",
     * @OA\Parameter(ref="#/components/parameters/badge"),
     *   tags={"Basket"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param Badge $badge
     *
     * @return JsonResponse
     */
    public function badgeCategoriesJson(Badge $badge): JsonResponse
    {
        $data = [];
        foreach (Category::where('hidden', false)->get() as $category) {
            $eggs = $category->projects()->whereHas(
                'badges',
                function ($query) use ($badge) {
                    $query->where('slug', $badge->slug);
                }
            )->whereHas(
                'versions',
                function ($query) {
                    $query->whereNotNull('zip');
                }
            );
            if ($eggs->exists()) {
                $data[] = [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'eggs' => $eggs->count(),
                ];
            }
        }

        return response()->json($data, 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the latest released versions in a category for a specific badge model.
     *
     * @OA\Get(
     *   path="/basket/{badge}/category/{category}/json",
     * @OA\Parameter(ref="#/components/parameters/badge"),
     * @OA\Parameter(
     *     name="category",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="utility")
     *   ),
     *   tags={"Basket"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param Badge    $badge
     * @param Category $category
     *
     * @return JsonResponse
     */
    public function badgeCategoryJson(Badge $badge, Category $category): JsonResponse
    {
        return response()->json(
            $badge->projects()->whereHas(
                'category',
                function ($query) use ($category) {
                    $query->where('slug', $category->slug);
                }
            )->whereHas(
                'versions',
                function ($query) {
                    $query->published();
                }
            )->orderBy('id', 'DESC')->get(),
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
    }
}
